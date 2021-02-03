<?php
/**
 * Created by PhpStorm.
 * User: kchapple
 * Date: 7/10/19
 * Time: 12:03 PM
 */

namespace Mi2\SFTP\Models;

class Batch
{
    private $id;
    private $startDatetime;
    private $endDatetime;
    private $serverId;

    private $files = [];

    /**
     * Batch constructor.
     * @param null $id
     * @param null $serverId
     * @param string $startDatetime
     * @param null $endDatetime
     */
    public function __construct($id, $serverId, $startDatetime, $endDatetime)
    {
        $this->id = $id;
        $this->serverId = $serverId;
        $this->startDatetime = $startDatetime;
        $this->endDatetime = $endDatetime;
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getStartDatetime(): string
    {
        return $this->startDatetime;
    }

    /**
     * @param string $startDatetime
     */
    public function setStartDatetime(string $startDatetime)
    {
        $this->startDatetime = $startDatetime;
    }

    /**
     * @return null
     */
    public function getEndDatetime()
    {
        return $this->endDatetime;
    }

    /**
     * @param null $endDatetime
     */
    public function setEndDatetime($endDatetime)
    {
        $this->endDatetime = $endDatetime;
    }

    /**
     * @return null
     */
    public function getServerId()
    {
        return $this->serverId;
    }

    /**
     * @param null $serverId
     */
    public function setServerId($serverId)
    {
        $this->serverId = $serverId;
    }

    public function addFile(File $file)
    {
        $this->files[]= $file;
    }

    public function getFiles()
    {
        return $this->files;
    }
}
