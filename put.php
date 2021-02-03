<?php
/**
 * Created by PhpStorm.
 * User: kchapple
 * Date: 6/28/19
 * Time: 12:41 PM
 */

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

// First check to make sure the fetch job isn't paused
$enabled = \Mi2\SFTP\Services\SFTPService::isFetchEnabled();
if ($enabled) {

    $server_id = $argv[1];
    if ($server_id === null) {
        echo "Server ID was not set\n";
        exit;
    }

    $path_of_file_to_put = $argv[2];
    if ($path_of_file_to_put === null) {
        echo "Path to file not set\n";
        exit;
    }

    if (!file_exists($path_of_file_to_put)) {
        echo "Local file does not exist\n";
        exit;
    }

    $server = \Mi2\SFTP\Services\SFTPService::getServer($server_id);
    if ($server === null) {
        echo "Server `$server_id` not found\n";
        exit;
    }

    $batch = new \Mi2\SFTP\Models\FetchFileBatch($server);
    $batch->put_file($path_of_file_to_put);

}
