<?php

namespace Admin\AdminBundle\EventListener;

use Avanzu\AdminThemeBundle\Event\ShowUserEvent;
use Avanzu\AdminThemeBundle\Model\UserModel;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserBarListener {

    /*protected $container;
    protected $security;

    public function __construct(ContainerInterface $container)
    {
        ldd($container);
        $this->container = $container;
    }*/


    public function onShowUser(ShowUserEvent $event) {

        //ldd($this->security);

        //$thisUser = $this->security->getToken()->getUser();

        //ldd($thisUser);

        $user = new UserModel();
        $user->setAvatar('')
             ->setIsOnline(false)
             ->setMemberSince(new \DateTime())
             ->setName("Nombre")
             ->setTitle("Titulo")
             ->setUsername('Demo User');

        //ldd($user);

        $event->setUser($user);
    }

}
