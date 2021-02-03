<?php
/**
 * Created by PhpStorm.
 * User: kchapple
 * Date: 7/9/19
 * Time: 9:54 AM
 */

namespace Mi2\SFTP\Models;


class SFTPServer
{
    protected $id;
    protected $host;
    protected $remoteDir;
    protected $username;
    protected $password;
    protected $deleteAfterFetch;
    protected $enabled;

    protected $sftp;

    /**
     * SFTPServer constructor.
     * @param $id
     * @param $host
     * @param $remoteDir
     * @param $username
     * @param $password
     */
    public function __construct($id, $host, $remoteDir, $username, $password, $deleteAfterFetch, $enabled)
    {
        $this->id = $id;
        $this->host = $host;
        $this->remoteDir = $remoteDir;
        $this->username = $username;
        $this->password = $password;
        $this->deleteAfterFetch = $deleteAfterFetch;
        $this->enabled = $enabled;
    }

    public function connect()
    {
        $this->sftp = new \phpseclib\Net\SFTP($this->getHost());
        $this->sftp->login($this->getUsername(), $this->getPassword());
        $this->sftp->chdir($this->getRemoteDir());
        return $this->sftp;
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
    public function getRemoteDir()
    {
        return $this->remoteDir;
    }

    /**
     * @param mixed $remoteDir
     */
    public function setRemoteDir($remoteDir)
    {
        $this->remoteDir = $remoteDir;
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
