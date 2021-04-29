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
$ignoreAuth = true;
$fake_register_globals = false;
$sanitize_all_escapes = true;
$_SESSION['site_id'] = 'default';
set_time_limit(0);

require_once(__DIR__."/../../../globals.php");

$server_id = $argv[1];
if ($server_id === null) {
    echo "Server ID was not set\n";
    exit;
}

$_SESSION['authUser'] = $server_id;

$server = \Mi2\SFTP\Services\SFTPService::makeServerUsingGlobalsId($server_id);
if ($server === null) {
    echo "Server `$server_id` not found\n";
    exit;
}

if ($server->isFetchEnabled()) {
    $batch = new \Mi2\SFTP\Models\FetchFileBatch($server);
    $batch->fetch();
}
