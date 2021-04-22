<?php
/**
 * Created by PhpStorm.
 * User: kchapple
 * Date: 7/9/19
 * Time: 7:49 AM
 */

namespace Mi2\SFTP\Models;

use Mi2\SFTP\Events\BeforeFetchEvent;
use Mi2\SFTP\Events\FetchedEvent;
use Mi2\SFTP\Events\FetchingEvent;
use Mi2\SFTP\Services\SFTPService;

class PutFileBatch
{
    private $server = null;
    private $batch;
    private $localStorageDir = 'documents/fetched_files';
    private $batchSize = 10;

    public function __construct(SFTPServer $server, Batch $batch, $localStorageDir = null, $batchSize = null)
    {
        $this->server = $server;
        $this->batch = $batch;
        $this->localStorageDir = $localStorageDir;
        if ($localStorageDir === null) {
            $this->localStorageDir = $server->getLocalPutDir();
        }

        $this->batchSize = $batchSize;
    }

    public function put_file($path_to_file)
    {
        // Don't do anything if this server isn't ebabled
        if ($this->getServer()->isPutEnabled() === false) {
            return "Put is not enabled on server `{$this->getServer()->getId()}`\n";
        }

        $filesize = filesize($path_to_file);
        $file = new File(null, $this->batch->getId(), $path_to_file, $filesize, File::FILE_STATUS_NEW, date('Y-m-d H:i:s'));
        $file = SFTPService::insertFile($file);

        // Make sure local claim file exists and can we have permission to read it
        // We try both the SFTP directory and the edi root directry
        if (!file_exists($path_to_file)) {
            return "File `$path_to_file` does not exist\n";
        }

        $put_file_contents = file_get_contents($path_to_file);
        if (false === $put_file_contents) {
            return "Could not read file `$path_to_file`\n";
        }

        // Attempt to login and change to remote directory
        $sftp = $this->getServer()->connect();
        if (false === $sftp) {
            $error = $sftp->getLastSFTPError();
            return "Could not connect\n$error\n";
        }

        if (false === $sftp->chdir($this->server->getRemotePutDir())) {
            $sftp->disconnect();
            $error = $sftp->getLastSFTPError();
            return "Could not change directory to `" . $this->server->getRemotePutDir() . "`\n$error\n";
        }

        $filename = basename($path_to_file);

        if (false === $sftp->put($filename, $put_file_contents)) {
            $sftp->disconnect();
            $error = $sftp->getLastSFTPError();

            // Put the file in the 'failure' directory
            $failure_dir = $GLOBALS['OE_SITE_DIR'] . DIRECTORY_SEPARATOR .
                'documents' . DIRECTORY_SEPARATOR .
                $this->localStorageDir . DIRECTORY_SEPARATOR . 'failure';
            SFTPService::createIfNotExists($failure_dir);
            $failure_filename = $failure_dir . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($failure_filename, $put_file_contents);

            // Remove the original downloaded file
            unlink($path_to_file);

            // Update the link to file
            $file->setFilename($failure_filename);
            SFTPService::updateFile($file);
            return "Could not put remote file file `" . $filename . "`\n$error\n";
        } else {
            // Put the file in the 'success' directory
            $success_dir = $GLOBALS['OE_SITE_DIR'] . DIRECTORY_SEPARATOR .
                'documents' . DIRECTORY_SEPARATOR .
                $this->localStorageDir . DIRECTORY_SEPARATOR . 'success';
            SFTPService::createIfNotExists($success_dir);
            $success_filename = $success_dir . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($success_dir . DIRECTORY_SEPARATOR . $filename, $put_file_contents);

            // Remove the original downloaded file
            unlink($path_to_file);

            // update the link to local file
            $file->setFilename($success_filename);
            SFTPService::updateFile($file);
        }

        // We were successful, Disconnect from the remote server
        $sftp->disconnect();
        $file->setStatus(File::FILE_STATUS_SUCCESS);
        SFTPService::updateFile($file);
        return true;
    }

    /**
     * @return SFTPServer|null
     */
    public function getServer()
    {
        return $this->server;
    }
}
