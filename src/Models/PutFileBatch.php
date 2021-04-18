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
            return "Could not put remote file file `" . $filename . "`\n$error\n";
        }

        // Disconnect from the remote server
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
