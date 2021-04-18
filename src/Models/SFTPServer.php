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
    protected $localPutDir; // Where files are stored locally to be "put" to remote SFTP
    protected $localFetchDir; // Where files are stored locally once they are "fetched" from remote SFTP
    protected $deleteAfterPut;
    protected $deleteAfterFetch;
    protected $putEnabled;
    protected $fetchEnabled;

    protected $sftp;

    protected $debugPrint = false;

    /**
     * SFTPServer constructor.
     * @param $id
     * @param $host
     * @param $username
     * @param $password
     * @param $remoteFetchDir
     * @param $remotePutDir
     * @param $localPutDir
     * @param $localFetchDir
     * @param $deleteAfterPut
     * @param $deleteAfterFetch
     * @param $putEnabled
     * @param $fetchEnabled
     */
    public function __construct($id, $host, $username, $password, $remoteFetchDir, $remotePutDir, $localPutDir, $localFetchDir, $deleteAfterPut, $deleteAfterFetch, $putEnabled, $fetchEnabled)
    {
        $this->id = $id;
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->remoteFetchDir = $remoteFetchDir;
        $this->remotePutDir = $remotePutDir;
        $this->localPutDir = $localPutDir;
        $this->localFetchDir = $localFetchDir;
        $this->deleteAfterPut = $deleteAfterPut;
        $this->deleteAfterFetch = $deleteAfterFetch;
        $this->putEnabled = ($putEnabled == 1 || $putEnabled == true) ? true : false;
        $this->fetchEnabled = ($fetchEnabled == 1 || $fetchEnabled == true) ? true : false;
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

    /**
     * @return mixed
     */
    public function getLocalFetchDir()
    {
        return $this->localFetchDir;
    }

    /**
     * @param mixed $localFetchDir
     */
    public function setLocalFetchDir($localFetchDir): void
    {
        $this->localFetchDir = $localFetchDir;
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
        $proc = shell_exec("$php $script '$server_id' '$file' > /dev/null 2>&1 &");
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

    /**
     * @return bool
     */
    public function isPutEnabled(): bool
    {
        return $this->putEnabled;
    }

    /**
     * @param bool $putEnabled
     */
    public function setPutEnabled(bool $putEnabled): void
    {
        $this->putEnabled = $putEnabled;
    }

    /**
     * @return bool
     */
    public function isFetchEnabled(): bool
    {
        return $this->fetchEnabled;
    }

    /**
     * @param bool $fetchEnabled
     */
    public function setFetchEnabled(bool $fetchEnabled): void
    {
        $this->fetchEnabled = $fetchEnabled;
    }


}
