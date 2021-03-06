<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Reservable\ActivityBundle\ReservableActivityBundle(),
            new Braincrafted\Bundle\BootstrapBundle\BraincraftedBootstrapBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new User\UserBundle\UserUserBundle(),
            new Bookings\BookingBundle\BookingsBookingBundle(),
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
            new JavierEguiluz\Bundle\EasyAdminBundle\EasyAdminBundle(),
            new Avanzu\AdminThemeBundle\AvanzuAdminThemeBundle(),
            new Admin\AdminBundle\AdminAdminBundle(),
            new Ragings\RatingBundle\RagingsRatingBundle(),
            new Ob\HighchartsBundle\ObHighchartsBundle(),
            new Ivory\GoogleMapBundle\IvoryGoogleMapBundle(),
            new BOMO\IcalBundle\BOMOIcalBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    /*public function init(){
        date_default_timezone_set('Europe/Madrid');
        parent::init();
    }*/

    /*public function __construct($enviroment, $debug){
        parent::__construct($enviroment, $debug);
        date_default_timezone_set('Europe/Madrid');
    }*/
}
