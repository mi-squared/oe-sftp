<?php
/**
 * Created by PhpStorm.
 * User: kchapple
 * Date: 7/10/19
 * Time: 12:25 PM
 */

namespace Mi2\SFTP\Models;


class File
{
    private $id;
    private $batchId;
    private $filename;
    private $filesize;
    private $status;
    private $createdDate;

    /**
     * File constructor.
     * @param $id
     * @param $batchId
     * @param $filename
     * @param $filesize
     */
    public function __construct($id, $batchId, $filename, $filesize, $status, $createdDate)
    {
        $this->id = $id;
        $this->batchId = $batchId;
        $this->filename = $filename;
        $this->filesize = $filesize;
        $this->status = $status;
        $this->createdDate = $createdDate;
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
    public function getBatchId()
    {
        return $this->batchId;
    }

    /**
     * @param mixed $batchId
     */
    public function setBatchId($batchId)
    {
        $this->batchId = $batchId;
    }

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param mixed $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return mixed
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

    /**
     * @param mixed $filesize
     */
    public function setFilesize($filesize)
    {
        $this->filesize = $filesize;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param mixed $createdDate
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    }
}
