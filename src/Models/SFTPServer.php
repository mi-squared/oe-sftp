<?php
/**
 * Created by PhpStorm.
 * User: kchapple
 * Date: 7/9/19
 * Time: 9:54 AM
 */

namespace Mi2\SFTP\Models;


use Mi2\SFTP\Services\SFTPService;

class SFTPServer
{
    protected $id;
    protected $host;
    protected $username;
    protected $password;
    protected $remoteFetchDir;
    protected $remotePutDir;
    protected $localPutDir;
    protected $deleteAfterPut;
    protected $deleteAfterFetch;
    protected $enabled;

    protected $sftp;

    protected $debugPrint = false;

    /**
     * SFTPServer constructor.
     * @param $id
     * @param $host
     * @param $remoteDir
     * @param $username
     * @param $password
     */
    public function __construct($id, $host, $username, $password, $remoteFetchDir, $remotePutDir, $localPutDir, $deleteAfterPut, $deleteAfterFetch, $enabled)
    {
        $this->id = $id;
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->remoteFetchDir = $remoteFetchDir;
        $this->remotePutDir = $remotePutDir;
        $this->localPutDir = $localPutDir;
        $this->deleteAfterPut = $deleteAfterPut;
        $this->deleteAfterFetch = $deleteAfterFetch;
        $this->enabled = ($enabled == 1 || $enabled == true) ? true : false;
    }

    /**
     * @return mixed
     */
    public function getRemoteFetchDir()
    {
        return $this->remoteFetchDir;
    }

    /**
     * @param mixed $remoteFetchDir
     */
    public function setRemoteFetchDir($remoteFetchDir): void
    {
        $this->remoteFetchDir = $remoteFetchDir;
    }

    /**
     * @return mixed
     */
    public function getRemotePutDir()
    {
        return $this->remotePutDir;
    }

    /**
     * @param mixed $remotePutDir
     */
    public function setRemotePutDir($remotePutDir): void
    {
        $this->remotePutDir = $remotePutDir;
    }

    /**
     * @return mixed
     */
    public function getLocalPutDir()
    {
        return $this->localPutDir;
    }

    /**
     * @param mixed $localPutDir
     */
    public function setLocalPutDir($localPutDir): void
    {
        $this->localPutDir = $localPutDir;
    }

    public function connect()
    {
        $this->sftp = new \phpseclib\Net\SFTP($this->getHost());
        $this->sftp->login($this->getUsername(), $this->getPassword());
        return $this->sftp;
    }


    public function file_put_contents_background($file_contents, $ext)
    {
        $directory = $GLOBALS['OE_SITE_DIR'] . DIRECTORY_SEPARATOR .
            'documents' . DIRECTORY_SEPARATOR .
            $this->getLocalPutDir();

        if (!file_exists($directory)) {
            SFTPService::createIfNotExists($directory, 0755);
        }

        // Get the out-dir of this server.
        $file = $directory . DIRECTORY_SEPARATOR .
            uniqid() . '.' . $ext;

        // Write the file to disk
        file_put_contents($file, $file_contents);
        $binary_path = $GLOBALS['perl_bin_dir'];
        $php = $binary_path . '/php';
        $server_id = $this->getId();
        // Tell the server to SFTP the file
        $script = __DIR__ . "/../../put.php";
        $proc = shell_exec("$php $script '$server_id' '$file' &");
        if ($this->debugPrint) {
            echo $proc;
        }
    }

    /**
     * @return mixed
     */
    public function getSftp()
    {
        return $this->sftp;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getDeleteAfterPut()
    {
        return $this->deleteAfterPut;
    }

    /**
     * @param mixed $deleteAfterPut
     */
    public function setDeleteAfterPut($deleteAfterPut): void
    {
        $this->deleteAfterPut = $deleteAfterPut;
    }

    /**
     * @return mixed
     */
    public function getDeleteAfterFetch()
    {
        return $this->deleteAfterFetch;
    }

    /**
     * @param mixed $deleteAfterFetch
     */
    public function setDeleteAfterFetch($deleteAfterFetch)
    {
        $this->deleteAfterFetch = $deleteAfterFetch;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }
}
