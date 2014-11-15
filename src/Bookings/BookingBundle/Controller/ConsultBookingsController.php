<?php

namespace Bookings\BookingBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Bookings\BookingBundle\Entity\Booking;
use Bookings\BookingBundle\Entity\DisponibilityBooking;

class ConsultBookingsController extends Controller
{
	public function consultBookingsAction()
	{
		if(!$this->get('security.context')->isGranted('ROLE_USER')) {
			throw new AccessDeniedException();
		}

		$ownerID = $this->get('security.context')->getToken()->getUser()->getId();

		$ownerProperties = $this->getDoctrine()
                           ->getRepository('ReservableActivityBundle:Activity')
                           ->findAllByOwnerID($ownerID);

        $arrayProperties = array();
        foreach($ownerProperties as $oneResult){$arrayProperties[] = $oneResult->getId();}

		$allBookings = $this->getDoctrine()
                            ->getRepository('BookingsBookingBundle:Booking')
                            ->getBookingsFromProperties($arrayProperties);

        $results = array();
        foreach($allBookings as $oneBooking){
        	$propertyData 	= $this->getDoctrine()
                            	->getRepository('ReservableActivityBundle:Activity')
                            	->findByPropertyID($oneBooking->getActivityID());

            $clientData 	= $this->getDoctrine()
                       			->getRepository('UserUserBundle:Users')
                       			->getUserByUserID($oneBooking->getClientID());

            $aux['propertyID'] 		= $oneBooking->getActivityID();
            $aux['clientID'] 		= $oneBooking->getClientID();
            $aux['price'] 			= $oneBooking->getPrice();
            $aux['startDate'] 		= $oneBooking->getStartDate();
            $aux['startDateDay'] 	= substr($oneBooking->getStartDate(), 6, 2);
            $aux['startDateMonth']	= substr($oneBooking->getStartDate(), 4, 2);
            $aux['startDateYear'] 	= substr($oneBooking->getStartDate(), 0, 4);
            $aux['startDateHour'] 	= substr($oneBooking->getStartDate(), 8, 2);
            $aux['endDate'] 		= $oneBooking->getEndDate();
            $aux['endDateDay'] 		= substr($oneBooking->getEndDate(), 6, 2);
            $aux['endDateMonth']	= substr($oneBooking->getEndDate(), 4, 2);
            $aux['endDateYear'] 	= substr($oneBooking->getEndDate(), 0, 4);
            $aux['endDateHour'] 	= substr($oneBooking->getEndDate(), 8, 2);

            $aux['type'] 			= $propertyData->getTypeRent();
            $aux['propertyName'] 	= $propertyData->getName();

            $aux['clientName'] 		= $clientData->getName();
            $aux['clientSurname'] 	= $clientData->getSurname();
            $aux['clientEmail'] 	= $clientData->getEmail();

            $results[] = $aux;
        }

		return $this->render('BookingsBookingBundle:Consult:see-bookings.html.twig', 
			array('bookings' => $results));
	}
}