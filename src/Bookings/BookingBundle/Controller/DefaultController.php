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
                    ->setFromIcalID(0)
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

		$em->flush();

		// Preparacion datos para enviar emails de confirmación
		$property =  $this->getDoctrine()
                          ->getRepository('ReservableActivityBundle:Activity')
                          ->findByPropertyID($_POST['itemID']);

        $client = $this->getDoctrine()
                       ->getRepository('UserUserBundle:Users')
                       ->getUserByUserID($thisBooking->getClientID());

        $owner = $this->getDoctrine()
                       ->getRepository('UserUserBundle:Users')
                       ->getUserByUserID($property->getOwnerID());

        list($SDday, $SDmonth, $SDyear, $SDhour) = array(substr($thisBooking->getStartDate(), 6, 2),substr($thisBooking->getStartDate(), 4, 2),substr($thisBooking->getStartDate(), 0, 4),substr($thisBooking->getStartDate(), 8, 2));
        list($EDday, $EDmonth, $EDyear, $EDhour) = array(substr($thisBooking->getEndDate(), 6, 2),substr($thisBooking->getEndDate(), 4, 2),substr($thisBooking->getEndDate(), 0, 4),substr($thisBooking->getEndDate(), 8, 2));

        // Email para el propietario
        $ownermsg = new \Swift_Message();
        $text = '<h1>Ha recibido una nueva reserva</h1><br/><br/>';
        $text .= '<strong>N&uacute;mero de reserva</strong>: ' . $thisBookingID;

        $text .= '<br/><strong>Propiedad</strong>: ' 	. $property->getName();
        $text .= '<br/><strong>Precio</strong>: ' 	. $thisBooking->getPrice() . ' &euro;';

        if($property->getTypeRent() == 'hour'){
        	$text .= '<br/><strong>D&iacute;a</strong> '. $SDday . "/" . $SDmonth . "/" . $SDyear;
        	$text .= '<br/><strong>Hora</strong>: ' 	. $SDhour;
        }
        else{
        	$text .= '<br/><strong>Desde</strong>: ' 	. $SDday . "/" . $SDmonth . "/" . $SDyear;
	        $text .= '<br/><strong>Hasta</strong>: ' 	. $EDday . "/" . $EDmonth . "/" . $EDyear;
        }
        
        $text .= '<br/><strong>Cliente</strong>: ' 	. $client->getName();
        $text .= '<br/><strong>Email</strong>: ' 	. $client->getEmail();
        if(!empty($client->getPhoneNumber())) 		$text .= '<br/><strong>Teléfono</strong>: ' . $client->getPhoneNumber();
        if(!empty($client->getMobileNumber())) 		$text .= '<br/><strong>Teléfono</strong>: ' . $client->getMobileNumber();

        $text .= '<br/><br/><p>Pulse aqu&iacute; para confirmar o rechazar esta reserva</p>';
        $userEmail = $this->get('security.context')->getToken()->getUser()->getEmail();;
        $ownermsg->setContentType ('text/html')
        		->setSubject ('Nueva reserva')
            	->setFrom('almacenpfcs@gmail.com')
            	->setTo($owner->getEmail())
            	->setBody($text);
        $this->get('mailer')->send($ownermsg);

        // Email para el cliente
        $clientmsg = new \Swift_Message();
        $textClient = '<h1>Estos son los detalles de su reserva</h1><br/><br/>';
        $textClient .= '<strong>N&uacute;mero de reserva</strong>: ' . $thisBookingID;

        $textClient .= '<br/><strong>Propiedad</strong>: ' 	. $property->getName();
        $textClient .= '<br/><strong>Precio</strong>: ' 		. $thisBooking->getPrice() . ' &euro;';

        if($property->getTypeRent() == 'hour'){
        	$textClient .= '<br/><strong>D&iacute;a</strong> '. $SDday . "/" . $SDmonth . "/" . $SDyear;
        	$textClient .= '<br/><strong>Hora</strong>: ' 	. $SDhour;
        }
        else{
        	$textClient .= '<br/><strong>Desde</strong>: ' 	. $SDday . "/" . $SDmonth . "/" . $SDyear;
	        $textClient .= '<br/><strong>Hasta</strong>: ' 	. $EDday . "/" . $EDmonth . "/" . $EDyear;
        }
        
        $textClient .= '<br/><br/><p>Hemos enviado una notificaci&oacuten al propietario, tan pronto como le sea posible se pondr&aacute; en contacto con usted para ultimar los detalles de su reserva.</p>';
        $clientmsg->setContentType ('text/html')
        		->setSubject ('Detalles de su reserva')
            	->setFrom('almacenpfcs@gmail.com')
            	->setTo($client->getEmail())
            	->setBody($textClient);
        $this->get('mailer')->send($clientmsg);

		return $this->render('BookingsBookingBundle:Default:bookingConfirmed.html.twig', array('bookingID' => $thisBooking->getId()));
	}
}