<?php

namespace Bookings\BookingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BookingsBookingBundle:Default:index.html.twig', array('name' => $name));
    }
}
