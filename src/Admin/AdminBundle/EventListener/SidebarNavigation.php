<?php
namespace Admin\AdminBundle\EventListener;

use Avanzu\AdminThemeBundle\Event\SidebarMenuEvent;
use Avanzu\AdminThemeBundle\Model\MenuItemModel;
use Symfony\Component\HttpFoundation\Request;

class SidebarNavigation
{

    protected $context;

    public function __construct($context)
    {
        $this->context = $context;
    }

    public function onSetupMenu(SidebarMenuEvent $event)
    {
        $request = $event->getRequest();

        foreach ($this->getMenu($request) as $item) {
            $event->addItem($item);
        }
    }


    protected function getMenu(Request $request)
    {
        $menu      = array();
        $rootItems = array();
        if($this->context->isGranted('ROLE_SUPER_ADMIN')){
            //$rootItems[] = $viewOwners      = new MenuItemModel('view-owners',      'Ver propietarios',         'view_owners',      $menu, 'glyphicon glyphicon-eye-open');
            $rootItems[] = $viewUsers      = new MenuItemModel('view-users',        'Ver usuarios',             'view_users',       $menu, 'glyphicon glyphicon-eye-open');
        }

        $rootItems[] = $viewProperties     = new MenuItemModel('view-activities',  'Ver propiedades',          'view_activities',  $menu, 'glyphicon glyphicon-eye-open');
        $rootItems[] = $addProperty        = new MenuItemModel('add-property',     'Nueva ropiedad',           'new_activity',     $menu, 'glyphicon glyphicon-plus');
        $rootItems[] = $bookings           = new MenuItemModel('bookings',         'Reservas',                 'consultBookings',  $menu, 'glyphicon glyphicon-check');
        $rootItems[] = $calendar           = new MenuItemModel('calendarBookings', 'Calendario',               'calendarBookings', $menu, 'fa-calendar');
        $rootItems[] = $history            = new MenuItemModel('historyBookings',  'Historial de reservas',    'historyBookings',  $menu, 'fa fa-th');
        $rootItems[] = $statistics         = new MenuItemModel('statistics',       'Estadísticas',             'historyBookings',  $menu, 'fa fa-bar-chart');

        /*$rootItems = array(
            $viewProperties     = new MenuItemModel('view-activities',  'Ver propiedades',          'view_activities',  $menu, 'glyphicon glyphicon-eye-open'),
            $addProperty        = new MenuItemModel('add-property',     'Nueva ropiedad',           'new_activity',     $menu, 'glyphicon glyphicon-plus'),
            $bookings           = new MenuItemModel('bookings',         'Reservas',                 'consultBookings',  $menu, 'glyphicon glyphicon-check'),
            $calendar           = new MenuItemModel('calendarBookings', 'Calendario',               'calendarBookings', $menu, 'fa-calendar'),
            $history            = new MenuItemModel('historyBookings',  'Historial de reservas',    'historyBookings',  $menu, 'fa fa-th'),
            $statistics         = new MenuItemModel('statistics',       'Estadísticas',             'historyBookings',  $menu, 'fa fa-bar-chart')
        );*/

        //$statistics->addChild(new MenuItemModel('ui-elements-general', 'General', 'avanzu_admin_ui_gen_demo', $earg))
        //           ->addChild($icons = new MenuItemModel('ui-elements-icons', 'Icons', 'avanzu_admin_ui_icon_demo', $earg));

        return $this->activateByRoute($request->get('_route'), $rootItems);

    }

    protected function activateByRoute($route, $items) {

        foreach($items as $item) { /** @var $item MenuItemModel */
            if($item->hasChildren()) {
                $this->activateByRoute($route, $item->getChildren());
            }
            else {
                if($item->getRoute() == $route) {
                    $item->setIsActive(true);
                }
            }
        }

        return $items;
    }


}