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

class FetchFileBatch
{
    private $server = null;
    private $localStorageDir = 'documents/fetched_files';
    private $batchSize = 10;

    public function __construct(SFTPServer $server, $localStorageDir, $batchSize)
    {
        $this->server = $server;
        $this->localStorageDir = $localStorageDir;
        $this->batchSize = $batchSize;
    }

    public function put_file($path_to_file)
    {
        // Don't do anything if this server isn't ebabled
        if ($this->getServer()->isEnabled() === false) {
            return;
        }

        // Attempt to login and change to remote directory
        $sftp = $this->getServer()->connect();
        if (false === $sftp) {
            die("Could not connect\n");
        }

        $sftp->put($path_to_file);

    }

    public function fetch()
    {
        // Don't do anything if this server isn't ebabled
        if ($this->getServer()->isEnabled() === false) {
            return;
        }

        $beforeFecthEvent = new BeforeFetchEvent();
        $beforeFecthEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch(BeforeFetchEvent::EVENT_HANDLE, $beforeFecthEvent, 10);

        // Connect to remote server, and return and SFTP instance
        $sftp = $this->getServer()->connect();
        $rlist = $sftp->rawlist();

        $new = 0;
        if (count($rlist) > 0) {

            // Create a new batch entity for this batch of files in the database,
            // This sets the batch ID and start_timestamp
            $batch = new Batch(null, $this->server->getId(), date('Y-m-d H:i:s'), null);
            $batch = SFTPService::insertBatch($batch);

            foreach ($rlist as $fname => $fattr) {
                if ($new < $this->batchSize) {

                    $fsize = $sftp->filesize($fname);
                    $fetchingEvent = new FetchingEvent($fname, $fsize, $this->getServer());
                    $fetchingEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch(FetchingEvent::EVENT_HANDLE, $fetchingEvent, 10);

                    if ($fetchingEvent->doFetch()) {

                        // Create a path to where we are going to store the local file
                        // Path includes the [storage]/batchId/basename(file)
                        $localPath = $this->localStorageDir.DIRECTORY_SEPARATOR.$batch->getId();
                        SFTPService::createIfNotExists($localPath, 0755);
                        $localFile = $localPath.DIRECTORY_SEPARATOR.$fname;
                        if ($sftp->get($fname, $localFile) === false) {
                            print_r($sftp->getSFTPErrors());
                            error_log("FetchFiles: Encountered while retrieving '$fname' from server!!");
                            continue;
                        }

                        // Store data about this file
                        $filesize = filesize($localFile);
                        $file = new File(null, $batch->getId(), $localFile, $filesize, "new", date('Y-m-d H:i:s'));
                        $file = SFTPService::insertFile($file);

                        // The file has been fetched, and exists on our local system
                        $fetchedEvent = new FetchedEvent($file, $this->getServer());
                        $fetchedEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch(FetchedEvent::EVENT_HANDLE, $fetchedEvent, 10);

                        // Record messages
                        foreach ($fetchedEvent->getMessages() as $message) {
                            SFTPService::insertMessage($file, $message);
                        }

                        // have local copy so delete remote original by default, but this can be overridden in the listener
                        if ($fetchedEvent->doRemoteDelete()) {
                            $sftp->delete($fname);
                        }

                        $new++;
                    } else if ($fetchingEvent->doRemoteDelete()) {
                        // Should we delete, even if we don't fetch ?!?!?!
                        $sftp->delete($fname);
                    }
                } else {
                    // stop fetching because we've reached our max for this batch
                    break;
                }
            }

            // Set the end time of the batch and store
            $batch->setEndDatetime(date('Y-m-d H:i:s'));
            SFTPService::updateBatch($batch);
        }
    }

    /**
     * @return SFTPServer|null
     */
    public function getServer()
    {
        return $this->server;
    }
}
