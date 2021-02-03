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
 * Event object for adding behavior before any files are fetched or connection to server is made
 *
 * @package OpenEMR\Events
 * @subpackage SFTP
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class SFTPBootEvent extends Event
{
    const EVENT_HANDLE = 'sftp.boot';

    protected $registeredServers = [];

    public function registerServer(SFTPServer $server)
    {
        $this->registeredServers[] = $server;
    }

    public function getRegisteredServers()
    {
        return $this->registeredServers;
    }
}
