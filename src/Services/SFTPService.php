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
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Services\Globals\GlobalSetting;

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

    public static function put($server_id, $file_contents, $ext = 'json', $filename = null)
    {
        $server = self::makeServerUsingGlobalsId($server_id);
        return $server->file_put_contents_background($file_contents, $ext, $filename);
    }

    /**
     * Build a Global configuration for an SFTP server.
     *
     * @param GlobalsInitializedEvent $event
     * @param $name Specify the name of the server (for labels)
     * @param $id Specify the ID of the server (for retrieving)
     * @throws \Exception
     */
    public static function buildServerGlobalConfig(GlobalsInitializedEvent $event, $name, $id)
    {
        try {
            $event->getGlobalsService()->createSection("SFTP", "Connectors");
        } catch (\Exception $e) {
            // It's ok if the section exists
        }

        $setting = new GlobalSetting( "$name SFTP Put Enable", 'bool', false, "Enable SFTP sending" );
        $event->getGlobalsService()->appendToSection( "SFTP", "oe_sftp_server_enable_$id", $setting );

        $setting = new GlobalSetting( "$name SFTP Fetch Enable", 'bool', false, "Enable SFTP receiving" );
        $event->getGlobalsService()->appendToSection( "SFTP", "oe_sftp_server_fetch_enable_$id", $setting );

        $setting = new GlobalSetting( "$name SFTP Delete After Put", 'bool', false, "Delete local files after putting" );
        $event->getGlobalsService()->appendToSection( "SFTP", "oe_sftp_server_delete_put_$id", $setting );

        $setting = new GlobalSetting( "$name SFTP Delete After Fetch", 'bool', false, "Delete remote files after fetching them" );
        $event->getGlobalsService()->appendToSection( "SFTP", "oe_sftp_server_delete_fetch_$id", $setting );

        $setting = new GlobalSetting( "$name SFTP Username", 'text', "openemr_service", "SFTP login" );
        $event->getGlobalsService()->appendToSection( "SFTP", "oe_sftp_server_username_$id", $setting );

        $setting = new GlobalSetting( "$name SFTP Password", 'text', "******", "SFTP Password" );
        $event->getGlobalsService()->appendToSection( "SFTP", "oe_sftp_server_password_$id", $setting );

        $setting = new GlobalSetting( "$name SFTP Host", 'text', "oprepo.officepracticum.com", "Remote host" );
        $event->getGlobalsService()->appendToSection( "SFTP", "oe_sftp_server_host_$id", $setting );

        $setting = new GlobalSetting( "$name SFTP Port", 'text', 22, "Remote Port" );
        $event->getGlobalsService()->appendToSection( "SFTP", "oe_sftp_server_port_$id", $setting );

        $setting = new GlobalSetting( "$name SFTP Local Outbox Dir (files to be put)", 'text', "/$id/out", "Where OpenEMR places files to be put, relative to documents dir" );
        $event->getGlobalsService()->appendToSection( "SFTP", "oe_sftp_server_local_out_dir_$id", $setting );

        $setting = new GlobalSetting( "$name SFTP Local Inbox Dir (after files fetched)", 'text', "/$id/inbox", "Where OpenEMR places files after they are fetched, relative to documents dir" );
        $event->getGlobalsService()->appendToSection( "SFTP", "oe_sftp_server_local_in_dir_$id", $setting );

        $setting = new GlobalSetting( "$name SFTP Remote Dir Where OpenEMR Puts Files To", 'text', '/in', "Where NextStep receives input on remote host" );
        $event->getGlobalsService()->appendToSection( "SFTP", "oe_sftp_server_remote_in_dir_$id", $setting );

        $setting = new GlobalSetting( "$name SFTP Remote Dir Where OpenEMR Fetches Files From)", 'text', '/out', "Where NextStep places output on remote host" );
        $event->getGlobalsService()->appendToSection( "SFTP", "oe_sftp_server_remote_out_dir_$id", $setting );

        return $event;
    }

    public static function makeServerUsingGlobalsId($id)
    {
        $server = new SFTPServer(
            $id,
            $GLOBALS["oe_sftp_server_host_$id"],
            $GLOBALS["oe_sftp_server_username_$id"],
            $GLOBALS["oe_sftp_server_password_$id"],
            $GLOBALS["oe_sftp_server_remote_out_dir_$id"],
            $GLOBALS["oe_sftp_server_remote_in_dir_$id"],
            $GLOBALS["oe_sftp_server_local_out_dir_$id"],
            $GLOBALS["oe_sftp_server_local_in_dir_$id"],
            $GLOBALS["oe_sftp_server_delete_put_$id"],
            $GLOBALS["oe_sftp_server_delete_fetch_$id"],
            $GLOBALS["oe_sftp_server_enable_$id"],
            $GLOBALS["oe_sftp_server_fetch_enable_$id"]
        );

        return $server;
    }

    public static function insertBatch(Batch $batch)
    {
        $sql = "INSERT INTO `fetched_file_batches` SET
          `batch_type` = ?,
          `server_id` = ?,
          `start_datetime` = ?,
          `end_datetime` = ?";

        $batchId = sqlInsert($sql, [$batch->getBatchType(), $batch->getServerId(), $batch->getStartDatetime(), $batch->getEndDatetime()]);

        return self::fetchBatch($batchId);
    }

    public static function fetchBatch($batchId)
    {
        $sql = "SELECT * FROM `fetched_file_batches` WHERE `id` = ?";
        $row = sqlQuery($sql, [$batchId]);
        $batch = new Batch($row['id'], $row['server_id'], $row['batch_type'], $row['start_datetime'], $row['end_datetime']);
        return $batch;
    }

    public static function updateBatch(Batch $batch)
    {
        $sql = "UPDATE `fetched_file_batches` SET
          `server_id` = ?,
          `status` = ?,
          `batch_type` = ?,
          `start_datetime` = ?,
          `end_datetime` = ?
          WHERE `id` = ?";

        sqlStatement($sql, [$batch->getServerId(),$batch->getStatus(), $batch->getBatchType(), $batch->getStartDatetime(), $batch->getEndDatetime(), $batch->getId()]);

        return $batch;
    }

    public static function fetchMostRecentBatch()
    {
        $sql = "SELECT * FROM `fetched_file_batches` ORDER BY end_datetime DESC LIMIT 1";
        $row = sqlQuery($sql);
        $batch = new Batch($row['id'], $row['server_id'], $row['batch_type'], $row['start_datetime'], $row['end_datetime']);
        return $batch;
    }

    public static function insertBatchMessage(Batch $batch, $message)
    {
        if ($batch->getId() === null) {
            $batch = SFTPService::insertBatch($batch);
        }

        $sql = "INSERT INTO `fetched_file_files_meta` SET
          `file_id` = ?,
          `meta_key` = ?,
          `meta_value` = ?,
          `options` = ?";

        $metaId = sqlInsert($sql, [$batch->getId(), "batch_message", $message, ""]);
        return $metaId;
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

    public static function fetchMessagesForBatchId($batch_id)
    {
        $messages = [];
        $sql = "SELECT `meta_value` AS `message` FROM `fetched_file_files_meta` WHERE `meta_key` = 'batch_message' AND `file_id` = ?";
        $result = sqlStatement($sql, [$batch_id]);
        while ($row = sqlFetchArray($result)) {
            $messages[] = $row['message'];
        }
        return $messages;
    }

    public static function fetchMessagesForFileId($file_id)
    {
        $messages = [];
        $sql = "SELECT `meta_value` AS `message` FROM `fetched_file_files_meta` WHERE `meta_key` = 'message' AND `file_id` = ?";
        $result = sqlStatement($sql, [$file_id]);
        while ($row = sqlFetchArray($result)) {
            $messages[] = $row['message'];
        }
        return $messages;
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

    public static function updateFile(File $file)
    {
        $sql = "UPDATE `fetched_file_files` SET
          `batch_id` = ?,
          `filename` = ?,
          `filesize` = ?,
          `status` = ?,
          `date_created` = ? WHERE `id` = ?";

        $result = sqlStatement($sql, [$file->getBatchId(), $file->getFilename(), $file->getFilesize(), $file->getStatus(), $file->getCreatedDate(), $file->getId()]);

        return self::fetchFile($file->getId());
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

    public static function fetchFilesSince($date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d', strtotime('-1 week'));
        }

        $sql = "SELECT F.id as file_id, B.id as batch_id, B.server_id, B.batch_type, B.status as batch_status, F.status as file_status, F.filename, F.date_created AS file_created_date, B.start_datetime AS batch_created_date FROM `fetched_file_batches` B
            LEFT JOIN `fetched_file_files` F ON F.batch_id = B.id
            WHERE `B`.`start_datetime` >= ?";

        $result = sqlStatement($sql, [$date]);

        return $result;
    }

    public static function getColumns()
    {
        return [
            'Batch ID' => 'batch_id',
            'File ID' => 'file_id',
            'Server ID' => 'server_id',
            'Put/Fetch' => 'batch_type',
            'Status' => 'status',
            'File Name' => 'filename',
            'Created Time' => 'date_created',
            'Messages' => 'messages'
        ];
    }
}
