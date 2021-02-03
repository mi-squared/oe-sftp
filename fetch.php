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
echo "In fetch\n";
$ignoreAuth = true;
$fake_register_globals = false;
$sanitize_all_escapes = true;
$_SESSION['site_id'] = 'default';

require_once(__DIR__."/../../../globals.php");

// First check to make sure the fetch job isn't paused
$enabled = \Mi2\SFTP\Services\SFTPService::isFetchEnabled();
if ($enabled) {
    // Iterate over all servers and fetch a batch
    for ($iServerId = 1; $iServerId <= \Mi2\SFTP\Services\SFTPService::NUM_SERVERS; $iServerId++) {
        $batch = \Mi2\SFTP\Services\SFTPService::create($iServerId);
        $batch->fetch();
    }
}
