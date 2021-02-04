<?php

namespace Mi2\SFTP;

use Mi2\SFTP\Events\SFTPBootEvent;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Services\Globals\GlobalSetting;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventHandler
{
    protected $eventDispatcher = null;
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Initialize the modules event handlers
     */
    public function init()
    {
//        $container = $GLOBALS["kernel"]->getContainer();
//        $container->set('sftp_api', new Api());

        // Tell the system the event handler is initializing
        $sftpBootEvent = new SFTPBootEvent();
        $sftpBootEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch(SFTPBootEvent::EVENT_HANDLE, $sftpBootEvent, 10);
    }
}
