<?php

namespace Bookings\BookingBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Bookings\BookingBundle\Entity\Booking;
use Bookings\BookingBundle\Entity\DisponibilityBooking;

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

		if(!$_POST['price'] || empty($_POST['price']))
			die('ERROR: No se encuentra el precio');

		if(empty($this->get('security.context')->getToken()->getUser()->getId()))
			die('ERROR: Ningún cliente para la reserva');

		if($session->get('searchType') == 'hour'){
			if(!$session->get('searchStartDateComplete') || empty($session->get('searchStartDateComplete')))
				die('ERROR: Ninguna fecha inicial (tipo hora)');
		}
		elseif ($session->get('searchType') == 'day') {
			if(!$session->get('searchStartDateComplete') || empty($session->get('searchStartDateComplete')))
				die('ERROR: Ninguna fecha inicial (tipo dia)');

			if(!$session->get('searchEndDateComplete') || empty($session->get('searchEndDateComplete')))
				die('ERROR: Ninguna fecha final');
		}
		else
			die('ERROR: Ningun tipo seleccionado');

		if(!$session->get('searchDays') || empty($session->get('searchDays')))
			die('ERROR: Ningun intervalo de dias/horas');

		
		// guardamos la reserva
		$thisBooking = new Booking();
		$thisBooking->setActivityID($_POST['itemID'])
					->setClientID($this->get('security.context')->getToken()->getUser()->getId())
					->setStartDate($session->get('searchStartDateComplete'))
					->setEndDate($session->get('searchEndDateComplete'))
					->setPrice($_POST['price'])
					->setStatus(0)
					->setOwnerBooking(0)
					->setOwnerConfirm(0);

		$em = $this->getDoctrine()->getManager();
		$em->persist($thisBooking);

		$lastBookingID = $this->getDoctrine()
                              ->getRepository('BookingsBookingBundle:Booking')
                              ->getLastBookingID();

		if(empty($lastBookingID)) 	$thisBookingID = 1;
        else 						$thisBookingID = $lastBookingID[0][1] + 1;

		foreach($session->get('searchDays') as $oneDay){
			$oneItem = new DisponibilityBooking();
			$oneItem->setBookingID($thisBookingID)
					->setDate($oneDay);

			$em->persist($oneItem);
		}

		//$em->flush();

		return $this->render('BookingsBookingBundle:Default:bookingConfirmed.html.twig');
	}
}