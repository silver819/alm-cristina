<?php

namespace Bookings\BookingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function bookAction()
    {
        return $this->render('BookingsBookingBundle:Default:book.html.twig');
    }
}
