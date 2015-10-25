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
        if(!$this->context->isGranted('ROLE_SUPER_ADMIN')) {
            $rootItems[] = $viewUsers      = new MenuItemModel('fos_user_profile_show',        'Mi perfil',             'fos_user_profile_show',       $menu, 'glyphicon glyphicon-user');
        }
        if($this->context->isGranted('ROLE_SUPER_ADMIN')){
            $rootItems[] = $viewUsers      = new MenuItemModel('view-users',        'Ver usuarios',             'view_users',       $menu, 'fa fa-users');
        }

        if($this->context->isGranted('ROLE_ADMIN')) {
            $rootItems[] = $viewProperties = new MenuItemModel('view-activities', 'Ver propiedades', 'view_activities', $menu, 'fa fa-home');
        }
        $rootItems[] = $addProperty        = new MenuItemModel('add-property',     'Nueva propiedad',           'new_activity',    $menu, 'glyphicon glyphicon-plus');
        $rootItems[] = $bookings           = new MenuItemModel('bookings',         'Reservas',                 'consultBookings',  $menu, 'glyphicon glyphicon-check');
        if($this->context->isGranted('ROLE_ADMIN')) {
            $rootItems[] = $calendar = new MenuItemModel('calendarBookings', 'Calendario', 'calendarBookings', $menu, 'fa fa-calendar');
            $rootItems[] = $history = new MenuItemModel('historyBookings', 'Historial de reservas', 'historyBookings', $menu, 'fa fa-calendar-check-o');
        }
        if($this->context->isGranted('ROLE_SUPER_ADMIN')){
            $rootItems[] = $viewOwners      = new MenuItemModel('admin-types',     'Gestionar tipos',           'admin_types',      $menu, 'fa fa-text-width');
            $rootItems[] = $viewOwners      = new MenuItemModel('admin-features',  'Gestionar características', 'admin_features',   $menu, 'fa fa-list-alt');
            $rootItems[] = $viewOwners      = new MenuItemModel('types-features',  'Tipos y características',   'new_features',     $menu, 'fa fa-exchange');
            $rootItems[] = $viewOwners      = new MenuItemModel('admin-zones',     'Gestionar zonas',           'admin_zones',      $menu, 'fa fa-map-o');
        }
        if($this->context->isGranted('ROLE_ADMIN')) {
            $rootItems[] = $statistics = new MenuItemModel('statistics', 'Estadísticas', 'statistics', $menu, 'fa fa-bar-chart');
        }

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