<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Mi2\SFTP\Events;

use Mi2\SFTP\Models\SFTPServer;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for adding behavior when files are fetched
 *
 * @package OpenEMR\Events
 * @subpackage Appointments
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class FetchingEvent extends Event
{
    /**
     * The customFilter event occurs in the library/appointments.inc.php file in the fetchEvents()
     * function, which fetches an array of all appointments. Setting this object's customWhereFilter
     * can filter appointments that show up.
     */
    const EVENT_HANDLE = 'fetchFiles.fetching';

    private $server = null;

    private $filename = null;

    private $filesize = 0;

    private $doFetch = true;

    private $doRemoteDelete = false; // Should we remote delete no matter what, even if we don't fetch? most likely not.

    /**
     *
     */
    public function __construct($filename, $filesize, SFTPServer $server)
    {
        $this->filename = $filename;
        $this->filesize = $filesize;
        $this->server = $server;
        $this->init();
    }

    public function init()
    {
        if ($this->filename == "." ||
            $this->filename == "..") {
            $this->doFetch = false;
        }
    }

    /**
     * @return null
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return int
     */
    public function getFilesize(): int
    {
        return $this->filesize;
    }

    /**
     * @return bool
     */
    public function doFetch(): bool
    {
        return $this->doFetch;
    }

    /**
     * @param bool $doFetch
     */
    public function setDoFetch(bool $doFetch)
    {
        $this->doFetch = $doFetch;
    }

    /**
     * @return bool
     */
    public function doRemoteDelete(): bool
    {
        return $this->doRemoteDelete;
    }

    /**
     * @param bool $doRemoteDelete
     */
    public function setDoRemoteDelete(bool $doRemoteDelete)
    {
        $this->doRemoteDelete = $doRemoteDelete;
    }

    /**
     * @return SFTPServer|null
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param SFTPServer|null $server
     */
    public function setServer(SFTPServer $server)
    {
        $this->server = $server;
    }
}
