<?php
/**
 * Created by PhpStorm.
 * User: kchapple
 * Date: 7/9/19
 * Time: 7:57 AM
 */

namespace Mi2\SFTP\Services;

use Mi2\SFTP\Models\Batch;
use Mi2\SFTP\Models\File;
use Mi2\SFTP\Models\FetchFileBatch;
use Mi2\SFTP\Models\SFTPServer;

class SFTPService
{
    const NUM_SERVERS = 3;

    const STATUS_NEW = 'new';

    public static function create($serverId)
    {
        $server = self::getServer($serverId);
        if (self::createIfNotExists($GLOBALS['ff_local_dir'], 0775) === false) {
            throw new \Exception("Could not create local storage directory '{$GLOBALS['ff_local_dir']}'");
        }

        $server->connect();

        return new FetchFileBatch($server, $GLOBALS['ff_local_dir'], $GLOBALS['ff_max_files']);
    }

    public static function createIfNotExists($path, $perms = 0644)
    {
        $success = true;
        if(!file_exists($path)) {
            $success = mkdir($path, $perms, true);
            chmod($path, $perms);
        }

        return $success;
    }

    public static function waitForFetchToComplete()
    {
        do {
            $lastFile = self::fetchMostRecentFile();
            $lastFileTime = new \DateTime($lastFile->getCreatedDate());
            $now = new \DateTime(date('Y-m-d H:i:s'));
            $interval = $now->diff($lastFileTime);
            $seconds = $interval->s;
            $seconds = $seconds + ($interval->days*24*60*60) + ($interval->hours*60*60) + ($interval->minutes*60);
            sleep(1);
        } while ($seconds < 30);

        return;
    }

    public static function isFetchEnabled()
    {
        $sql = "SELECT gl_value FROM globals WHERE gl_name = ? LIMIT 1";
        $row = sqlQuery($sql, ['ff_fetch_enabled']);
        return $row['gl_value'] == 1 ? true : false;
    }

    public static function setFetchEnabled($enabled = true)
    {
        $sql = "UPDATE globals SET gl_value = ? WHERE gl_name = ?";
        $result = sqlStatement($sql, [$enabled == true ? 1 : 0, 'ff_fetch_enabled']);
        return $enabled;
    }

    public static function isPutEnabled()
    {
        $sql = "SELECT gl_value FROM globals WHERE gl_name = ? LIMIT 1";
        $row = sqlQuery($sql, ['ff_put_enabled']);
        return $row['gl_value'] == 1 ? true : false;
    }

    public static function setPutEnabled($enabled = true)
    {
        $sql = "UPDATE globals SET gl_value = ? WHERE gl_name = ?";
        $result = sqlStatement($sql, [$enabled == true ? 1 : 0, 'ff_put_enabled']);
        return $enabled;
    }

    public static function getServer($serverId)
    {
        $serverId = $GLOBALS["ff_server_id_$serverId"];
        $host = $GLOBALS["ff_server_host_$serverId"];
        $remoteDir = $GLOBALS["ff_server_remote_dir_$serverId"];
        $username = $GLOBALS["ff_server_username_$serverId"];
        $password = $GLOBALS["ff_server_password_$serverId"];
        $deleteAfterFetch = ($GLOBALS["ff_server_remote_delete_$serverId"] == "1" ) ? true : false;
        $enabled = ($GLOBALS["ff_server_enable_$serverId"] == "1" ) ? true : false;

        return new SFTPServer($serverId, $host, $remoteDir, $username, $password, $deleteAfterFetch, $enabled);
    }

    public static function insertBatch(Batch $batch)
    {
        $sql = "INSERT INTO `fetched_file_batches` SET
          `server_id` = ?,
          `start_datetime` = ?,
          `end_datetime` = ?";

        $batchId = sqlInsert($sql, [$batch->getServerId(), $batch->getStartDatetime(), $batch->getEndDatetime()]);

        return self::fetchBatch($batchId);
    }

    public static function fetchBatch($batchId)
    {
        $sql = "SELECT * FROM `fetched_file_batches` WHERE `id` = ?";
        $row = sqlQuery($sql, [$batchId]);
        $batch = new Batch($row['id'], $row['server_id'], $row['start_datetime'], $row['end_datetime']);
        return $batch;
    }

    public static function updateBatch(Batch $batch)
    {
        $sql = "UPDATE `fetched_file_batches` SET
          `server_id` = ?,
          `start_datetime` = ?,
          `end_datetime` = ?
          WHERE `id` = ?";

        sqlStatement($sql, [$batch->getServerId(), $batch->getStartDatetime(), $batch->getEndDatetime(), $batch->getId()]);

        return $batch;
    }

    public static function fetchMostRecentBatch()
    {
        $sql = "SELECT * FROM `fetched_file_batches` ORDER BY end_datetime DESC LIMIT 1";
        $row = sqlQuery($sql);
        $batch = new Batch($row['id'], $row['server_id'], $row['start_datetime'], $row['end_datetime']);
        return $batch;
    }

    public static function insertMessage(File $file, $message)
    {
        $sql = "INSERT INTO `fetched_file_files_meta` SET
          `file_id` = ?,
          `meta_key` = ?,
          `meta_value` = ?,
          `options` = ?";

        $metaId = sqlInsert($sql, [$file->getId(), "message", $message, ""]);
        return $metaId;
    }

    public static function fetchFilesInBatch($batchId)
    {
        $files = [];
        $sql = "SELECT * FROM `fetched_file_files` WHERE `batch_id` = ?";
        $result = sqlStatement($sql, [$batchId]);
        while ($row = sqlFetchArray($result)) {
            $file = new File($row['id'], $row['batch_id'], $row['filename'], $row['filesize'], $row['status'], $row['date_created']);
            $files []= $file;
        }
        return $files;
    }

    public static function insertFile(File $file)
    {
        $sql = "INSERT INTO `fetched_file_files` SET
          `batch_id` = ?,
          `filename` = ?,
          `filesize` = ?,
          `status` = ?,
          `date_created` = ?";

        $fileId = sqlInsert($sql, [$file->getBatchId(), $file->getFilename(), $file->getFilesize(), $file->getStatus(), $file->getCreatedDate()]);

        return self::fetchFile($fileId);
    }

    public static function fetchFile($fileId)
    {
        $sql = "SELECT * FROM `fetched_file_files` WHERE `id` = ?";
        $row = sqlQuery($sql, [$fileId]);
        $file = new File($row['id'], $row['batch_id'], $row['filename'], $row['filesize'], $row['status'], $row['date_created']);
        return $file;
    }

    public static function fetchMostRecentFile()
    {
        $sql = "SELECT * FROM `fetched_file_files` ORDER BY date_created DESC LIMIT 1";
        $row = sqlQuery($sql);
        $file = new File($row['id'], $row['batch_id'], $row['filename'], $row['filesize'], $row['status'], $row['date_created']);
        return $file;
    }

    /**
     * @param $filename
     * @param $filesize
     * @return array
     *
     * Check the DB to see if we have another file with the same basename (could be different path)
     * and the same filesize.
     */
    public static function findFilesByNameAndSize($filename, $filesize)
    {
        $files = [];
        $basename = basename($filename);
        $sql = "SELECT * FROM `fetched_file_files` WHERE
          `filename` LIKE ? AND
          `filesize` = ?";
        $result = sqlStatement($sql, ["%$basename", $filesize]);
        while ($row = sqlFetchArray($result)) {
            $file = new File($row['id'], $row['batch_id'], $row['filename'], $row['filesize'], $row['status'], $row['date_created']);
            $files[]= $file;
        }
        return $files;
    }
}
