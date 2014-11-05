<?php

namespace Bookings\BookingBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
	public function bookAction()
	{

		if(!$this->get('security.context')->isGranted('ROLE_USER')) {
			throw new AccessDeniedException();
		}

		ladybug_dump($_POST);
		ladybug_dump($_SESSION);

		return $this->render('BookingsBookingBundle:Default:book.html.twig');
	}
}