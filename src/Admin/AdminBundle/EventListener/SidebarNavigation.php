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

        $arrayTexts = array();
        $arrayTexts['profile']          = array('es' => 'Mi perfil',    'en' => 'Profile');
        $arrayTexts['viewUsers']        = array('es' => 'Ver usuarios', 'en' => 'View users');
        $arrayTexts['viewProperties']   = array('es' => 'Ver propiedades', 'en' => 'View lodgings');
        $arrayTexts['newProperty']      = array('es' => 'Nueva propiedad', 'en' => 'New lodging');
        $arrayTexts['bookings']         = array('es' => 'Reservas', 'en' => 'Bookings');
        $arrayTexts['calendar']         = array('es' => 'Calendario', 'en' => 'Calendar');
        $arrayTexts['historical']       = array('es' => 'Historial de reservas', 'en' => 'Bookings history');
        $arrayTexts['types']            = array('es' => 'Tipos', 'en' => 'Types');
        $arrayTexts['features']         = array('es' => 'Características', 'en' => 'Features');
        $arrayTexts['typesFeatures']    = array('es' => 'Tipos y características', 'en' => 'Types & features');
        $arrayTexts['zones']            = array('es' => 'Zonas', 'en' => 'Zones');
        $arrayTexts['statistics']         = array('es' => 'estadísticas', 'en' => 'Statistics');

        $thisLang  = $request->getLocale();
        if(!$this->context->isGranted('ROLE_SUPER_ADMIN')) {

            $rootItems[] = $viewUsers      = new MenuItemModel('fos_user_profile_show',$arrayTexts['profile'][$thisLang],'fos_user_profile_show',$menu,'glyphicon glyphicon-user');
        }
        if($this->context->isGranted('ROLE_SUPER_ADMIN')){
            $rootItems[] = $viewUsers      = new MenuItemModel('view-users',$arrayTexts['viewUsers'][$thisLang],'view_users',$menu,'fa fa-users');
        }

        if($this->context->isGranted('ROLE_ADMIN')) {
            $rootItems[] = $viewProperties = new MenuItemModel('view-activities',$arrayTexts['viewProperties'][$thisLang], 'view_activities', $menu, 'fa fa-home');
        }
        $rootItems[] = $addProperty        = new MenuItemModel('add-property',$arrayTexts['newProperty'][$thisLang],           'new_activity',    $menu, 'glyphicon glyphicon-plus');
        $rootItems[] = $bookings           = new MenuItemModel('bookings',$arrayTexts['bookings'][$thisLang],                 'consultBookings',  $menu, 'glyphicon glyphicon-check');
        if($this->context->isGranted('ROLE_ADMIN')) {
            $rootItems[] = $calendar = new MenuItemModel('calendarBookings',$arrayTexts['calendar'][$thisLang], 'calendarBookings', $menu, 'fa fa-calendar');
            $rootItems[] = $history = new MenuItemModel('historyBookings',$arrayTexts['historical'][$thisLang], 'historyBookings', $menu, 'fa fa-clock-o');
        }
        if($this->context->isGranted('ROLE_SUPER_ADMIN')){
            $rootItems[] = $viewOwners      = new MenuItemModel('admin-types',$arrayTexts['types'][$thisLang],           'admin_types',      $menu, 'fa fa-text-width');
            $rootItems[] = $viewOwners      = new MenuItemModel('admin-features',$arrayTexts['features'][$thisLang], 'admin_features',   $menu, 'fa fa-list-alt');
            $rootItems[] = $viewOwners      = new MenuItemModel('types-features',$arrayTexts['typesFeatures'][$thisLang],   'new_features',     $menu, 'fa fa-exchange');
            $rootItems[] = $viewOwners      = new MenuItemModel('admin-zones',$arrayTexts['zones'][$thisLang],           'admin_zones',      $menu, 'fa fa-globe');
        }
        if($this->context->isGranted('ROLE_ADMIN')) {
            $rootItems[] = $statistics = new MenuItemModel('statistics',$arrayTexts['statistics'][$thisLang], 'statistics', $menu, 'fa fa-bar-chart');
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