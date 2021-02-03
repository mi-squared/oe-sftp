<?php

namespace Mi2\SFTP;

use Mi2\SFTP\Models\SFTPServer;

class Api
{


    public function put($server_id, $file_contents, $ext = 'json')
    {
        // Get the out-dir of this server.
        $file = __DIR__. DIRECTORY_SEPARATOR . uniqid() . '.' . $ext;

        // Write the file to disk
        file_put_contents($file, $file_contents);

        // Tell the server to SFTP the file
        $script = __DIR__ . "/../put.php";
        $proc = shell_exec("php $script '$server_id' '$file' &");
    }
}
