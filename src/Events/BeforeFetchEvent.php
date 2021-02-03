<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Mi2\SFTP\Events;

use Mi2\SFTP\Models\File;
use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for adding behavior before any files are fetched or connection to server is made
 *
 * @package OpenEMR\Events
 * @subpackage Appointments
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class BeforeFetchEvent extends Event
{
    const EVENT_HANDLE = 'fetchFiles.beforeFetch';

    /**
     *
     */
    public function __construct()
    {
        $this->init();
    }

    public function init()
    {

    }
}
