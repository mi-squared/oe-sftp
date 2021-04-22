<?php
/**
 * Created by PhpStorm.
 * User: kchapple
 * Date: 6/28/19
 * Time: 12:41 PM
 */

use Mi2\SFTP\Models\Batch;
use Mi2\SFTP\Services\SFTPService;

if (php_sapi_name() !== 'cli') {
    exit;
}

session_name("OpenEMR");
echo "In put\n";
$ignoreAuth = true;
$fake_register_globals = false;
$sanitize_all_escapes = true;
$_SESSION['site_id'] = 'default';

require_once(__DIR__."/../../../globals.php");



$server_id = $argv[1];
$batch = new Batch(null, $server_id, Batch::BATCH_TYPE_PUT, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));
$batch = SFTPService::insertBatch($batch);
if ($server_id === null) {
    $message = "Server ID was not set\n";
    SFTPService::insertBatchMessage($batch, $message);
    exit;
}

$path_of_file_to_put = $argv[2];
if ($path_of_file_to_put === null) {
    $message =  "Path to file not set\n";
    SFTPService::insertBatchMessage($batch, $message);
    exit;
}

if (!file_exists($path_of_file_to_put)) {
    $message = "Local file does not exist\n";
    SFTPService::insertBatchMessage($batch, $message);
    exit;
}

$server = SFTPService::makeServerUsingGlobalsId($server_id);
if ($server === null) {
    $message = "Server `$server_id` not found\n";
    SFTPService::insertBatchMessage($batch, $message);
    exit;
}

if ($server->isPutEnabled()) {
    $put = new \Mi2\SFTP\Models\PutFileBatch($server, $batch);
    $return = $put->put_file($path_of_file_to_put);
    if ($return === true) {
        $batch->setStatus(Batch::BATCH_STATUS_SUCCESS);
        SFTPService::updateBatch($batch);
    } else {
        // an error occured, and the put_file() function returned a message
        $batch->setStatus(Batch::BATCH_STATUS_ERROR);
        SFTPService::updateBatch($batch);
        SFTPService::insertBatchMessage($batch, $return);
    }
} else {
    // Server not enabled for put, should alert user
    $message = "SFTP put was not executed for `$path_of_file_to_put` on server `{$server_id}` because put is not enabled for server";
    SFTPService::insertBatchMessage($batch, $message);
}

echo "SUCCESS";
exit(0);

