<?php
/**
 * Bootstrap custom the Module, initialize event handler.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
$eventHandler = new \Mi2\SFTP\EventHandler($eventDispatcher);
$api = $eventHandler->init();
