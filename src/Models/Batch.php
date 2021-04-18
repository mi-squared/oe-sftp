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
    const BATCH_STATUS_NEW = 'batch.new';
    const BATCH_STATUS_ERROR = 'batch.error';
    const BATCH_STATUS_SUCCESS = 'batch.success';

    const BATCH_TYPE_FETCH = 'fetch';
    const BATCH_TYPE_PUT = 'put';

    private $id;
    private $startDatetime;
    private $endDatetime;
    private $serverId;
    private $batchType;
    private $status;

    private $files = [];

    /**
     * Batch constructor.
     * @param $id
     * @param $serverId
     * @param $batchType
     * @param $startDatetime
     * @param $endDatetime
     */
    public function __construct($id, $serverId, $batchType, $startDatetime, $endDatetime, $status = Batch::BATCH_STATUS_NEW)
    {
        $this->id = $id;
        $this->serverId = $serverId;
        $this->batchType = $batchType;
        $this->startDatetime = $startDatetime;
        $this->endDatetime = $endDatetime;
        $this->status = $status;
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

    /**
     * @return mixed
     */
    public function getBatchType()
    {
        return $this->batchType;
    }

    /**
     * @param mixed $batchType
     */
    public function setBatchType($batchType): void
    {
        $this->batchType = $batchType;
    }

    /**
     * @return mixed|string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed|string $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
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
