<?php

namespace Mi2\SFTP;

use Mi2\SFTP\Events\SFTPBootEvent;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Menu\MenuEvent;
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

        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, [$this, 'mainMenuUpdate'], 1);
    }

    public function mainMenuUpdate(MenuEvent $event)
    {
        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'sftp';
        $menuItem->menu_id = 'sftp0';
        $menuItem->label = xlt("SFTP");
        $menuItem->url = "/interface/modules/custom_modules/oe-sftp/index.php?action=sftp!import";
        $menuItem->children = [];
        $menuItem->acl_req = ["admin", "super"];

        foreach ($menu as $item) {
            if ($item->menu_id == 'admimg') {
                array_unshift($item->children, $menuItem);
                break;
            }
        }

        $event->setMenu($menu);

        return $event;
    }
}
