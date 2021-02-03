<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Mi2\SFTP\Events;

use Mi2\SFTP\Models\File;
use Mi2\SFTP\Models\SFTPServer;
use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for adding behavior when files are fetched
 *
 * @package OpenEMR\Events
 * @subpackage Appointments
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class FetchedEvent extends Event
{
    /**
     * The customFilter event occurs in the library/appointments.inc.php file in the fetchEvents()
     * function, which fetches an array of all appointments. Setting this object's customWhereFilter
     * can filter appointments that show up.
     */
    const EVENT_HANDLE = 'fetchFiles.fetched';

    private $file = null;

    private $server = null;

    private $messages = [];

    private $doRemoteDelete = false;

    /**
     *
     */
    public function __construct(File $file, SFTPServer $server)
    {
        $this->file = $file;
        $this->server = $server;
        $this->init();
    }

    public function init()
    {

    }

    /**
     * @return File|null
     */
    public function getFile()
    {
        return $this->file;
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
    public function setServer($server)
    {
        $this->server = $server;
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
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
    }
}
