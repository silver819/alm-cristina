<?php

namespace Bookings\BookingBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Bookings\BookingBundle\Entity\Booking;

class DefaultController extends Controller
{
	public function bookAction()
	{

		if(!$this->get('security.context')->isGranted('ROLE_USER')) {
			throw new AccessDeniedException();
		}

		return $this->render('BookingsBookingBundle:Default:book.html.twig', 
			array('propertyData' => $_POST));
	}

	public function confirmBookingAction(Request $request){
		$session 		= $request->getSession();

		// Comprobamos que todo esta OK
		if(!$_POST['itemID'] || empty($_POST['itemID']))
			die('ERROR: Ninguna propiedad encontrada');

		if(empty($this->get('security.context')->getToken()->getUser()->getId()))
			die('ERROR: Ningún cliente para la reserva');

		if(!$session->get('searchStartDateComplete') || empty($session->get('searchStartDateComplete')))
			die('ERROR: Ninguna fecha inicial');

		if(!$session->get('searchEndDateComplete') || empty($session->get('searchEndDateComplete')))
			die('ERROR: Ninguna fecha final');

		if(!$session->get('searchDays') || empty($session->get('searchDays')))
			die('ERROR: Ningun intervalo de dias/horas');

		
		// guardamos la reserva
		$thisBooking = new Booking();
		$thisBooking->setActivityID($_POST['itemID'])
					->setClientID($this->get('security.context')->getToken()->getUser()->getId())
					->setStartDate($session->get('searchStartDateComplete'))
					->setEndDate($session->get('searchEndDateComplete'))
					->setStatus(0)
					->setOwnerBooking(0)
					->setOwnerConfirm(0);

		$em = $this->getDoctrine()->getManager();
		$em->persist($thisBooking);



		
    	//$em->flush();

		return $this->render('BookingsBookingBundle:Default:bookingConfirmed.html.twig');
	}
}