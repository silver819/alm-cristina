<?php
namespace Admin\AdminBundle\EventListener;

use Avanzu\AdminThemeBundle\Event\SidebarMenuEvent;
use Avanzu\AdminThemeBundle\Model\MenuItemModel;
use Symfony\Component\HttpFoundation\Request;

class SidebarNavigation
{

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
        $rootItems = array(
            $viewProperties     = new MenuItemModel('view-activities',  'Ver propiedades',          'view_activities',  $menu, 'glyphicon glyphicon-eye-open'),
            $addProperty        = new MenuItemModel('add-property',     'Nueva ropiedad',           'new_activity',     $menu, 'glyphicon glyphicon-plus'),
            $bookings           = new MenuItemModel('bookings',         'Reservas',                 'consultBookings',  $menu, 'glyphicon glyphicon-check'),
            $calendar           = new MenuItemModel('calendarBookings', 'Calendario',               'calendarBookings', $menu, 'fa-calendar'),
            $history            = new MenuItemModel('historyBookings',  'Historial de reservas',    'historyBookings',  $menu, 'fa fa-th'),
            $statistics         = new MenuItemModel('statistics',       'Estadísticas',             'historyBookings',  $menu, 'fa fa-bar-chart')
        );

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