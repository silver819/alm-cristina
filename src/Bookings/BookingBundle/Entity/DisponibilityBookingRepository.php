<?php

namespace Bookings\BookingBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * DisponibilityBookingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DisponibilityBookingRepository extends EntityRepository
{
	function findDispoInThisRange($arrayBooking, $arrayDates){
		
		if(!empty($arrayDates) && !empty($arrayBooking)){
			$dates 		= implode(',', $arrayDates);
			
			$arrayBookingsIDs = array();
			foreach($arrayBooking as $oneBooking){
				$arrayBookingsIDs[] = $oneBooking->getId();
			}
			$bookings 	= implode(',', $arrayBookingsIDs);
			
			return $this->getEntityManager()
            	    	->createQuery('SELECT d
            	   					   FROM BookingsBookingBundle:DisponibilityBooking d
                					   WHERE d.date IN (' . $dates . ') AND d.bookingID IN (' . $bookings . ')')
            			->getResult();
		}
	}
}
