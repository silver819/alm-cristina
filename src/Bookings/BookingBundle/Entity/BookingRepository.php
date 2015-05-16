<?php

namespace Bookings\BookingBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Reservable\ActivityBundle\Entity\Activity;

/**
 * BookingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BookingRepository extends EntityRepository
{
	function findBookingsFromPropertyID($propertyID){
		return $this->getEntityManager()
            	    ->createQuery('SELECT b
            	   				   FROM BookingsBookingBundle:Booking b
                				   WHERE b.activityID = ' . $propertyID)
            		->getResult();
	}

	function getLastBookingID(){
		return $this->getEntityManager()
            	    ->createQuery('SELECT MAX(b.id)
            	   				   FROM BookingsBookingBundle:Booking b')
            		->getResult();
	}

    function getBookingsFromProperties($arrayProperties){
        if(!empty($arrayProperties)) {
            return $this->getEntityManager()
                ->createQuery('SELECT b
                                   FROM BookingsBookingBundle:Booking b
                                   WHERE b.activityID IN (' . implode(',', $arrayProperties) . ')
                                   AND b.ownerConfirm = 0')
                ->getResult();
        }
        else return false;
    }

    function getBookingID($bookingID){
      return $this->getEntityManager()
                    ->createQuery('SELECT b
                                   FROM BookingsBookingBundle:Booking b
                                   WHERE b.id = ' . $bookingID)
                    ->getResult();
    }

    function getBookingsFromPropertiesHistory($arrayProperties){
        if(!empty($arrayProperties)) {
            return $this->getEntityManager()
                ->createQuery('SELECT b
                                   FROM BookingsBookingBundle:Booking b
                                   WHERE b.activityID IN (' . implode(',', $arrayProperties) . ')')
                ->getResult();
        }
        else return false;
    }

    function acceptBooking($bookingID){
        return $this->getEntityManager()
                    ->createQuery('UPDATE BookingsBookingBundle:Booking b
                                  SET b.ownerConfirm = 1
                                  WHERE b.id = ' . $bookingID)
                    ->getResult();
    }

    function cancelBooking($bookingID){
        $result = $this->getEntityManager()
                    ->createQuery('DELETE FROM BookingsBookingBundle:DisponibilityBooking d
                                   WHERE d.bookingID = ' . $bookingID)->getResult();

        return $this->getEntityManager()
                    ->createQuery('UPDATE BookingsBookingBundle:Booking b
                                  SET b.ownerConfirm = -1
                                  WHERE b.id = ' . $bookingID)
                    ->getResult();
    }

    function getBookingsInPeriod($from, $to, $arrayProperties){
        $results = array();

        if(!empty($arrayProperties)) {
            $bookings = $this->getEntityManager()
                ->createQuery('SELECT b.id, b.startDate, b.endDate
                                        FROM BookingsBookingBundle:Booking b
                                        WHERE b.activityID IN (' . implode(',', $arrayProperties) . ')
                                        AND b.startDate >= ' . $from . ' 
                                        AND b.startDate < ' . $to . '
                                        AND b.ownerConfirm = 1')
                ->getResult();

            foreach ($bookings as $oneBooking) {
                $aux['bookingID'] = $oneBooking['id'];
                $aux['from'] = substr($oneBooking['startDate'], 6, 2);
                $monthStart = substr($oneBooking['startDate'], 4, 2);
                $monthEnd = substr($oneBooking['endDate'], 4, 2);
                $aux['to'] = ($monthEnd == $monthStart) ? substr($oneBooking['endDate'], 6, 2) : 50;
                $results[] = $aux;
            }
        }

      return $results;
    }
}
