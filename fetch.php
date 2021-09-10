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
$_SESSION['site'] = 'default';
$_SESSION['site_id'] = 'default';
$_SERVER['HTTP_HOST'] = 'localhost';
set_time_limit(0);

require_once(__DIR__."/../../../globals.php");

// Make sure there is not another fetch.php process running
$lock_file_directory = $GLOBALS['OE_SITE_DIR'] . DIRECTORY_SEPARATOR . 'documents';
$lock_file = fopen($lock_file_directory . 'fetch.pid', 'c');
$got_lock = flock($lock_file, LOCK_EX | LOCK_NB, $wouldblock);
if ($lock_file === false || (!$got_lock && !$wouldblock)) {
    throw new Exception(
        "Unexpected error opening or locking lock file. Perhaps you " .
        "don't  have permission to write to the lock file or its " .
        "containing directory?"
    );
}
else if (!$got_lock && $wouldblock) {
    exit("Another instance is already running; terminating.\n");
}

// Lock acquired; let's write our PID to the lock file for the convenience
// of humans who may wish to terminate the script.
ftruncate($lock_file, 0);
fwrite($lock_file, getmypid() . "\n");

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

// All done; we blank the PID file and explicitly release the lock
// (although this should be unnecessary) before terminating.
ftruncate($lock_file, 0);
flock($lock_file, LOCK_UN);
