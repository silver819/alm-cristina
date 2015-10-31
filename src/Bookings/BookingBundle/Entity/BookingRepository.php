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

    function findAllBookingsByClientID($ownerID){

        return $this->getEntityManager()
            ->createQuery('SELECT b
                		   FROM BookingsBookingBundle:Booking b
                		   WHERE b.clientID = ' . $ownerID)
            ->getResult();
    }

	function getLastBookingID(){
		return $this->getEntityManager()
            	    ->createQuery('SELECT MAX(b.id)
            	   				   FROM BookingsBookingBundle:Booking b')
            		->getResult();
	}

    function isIcalSync($bookingID){
		$result =  $this->getEntityManager()
            	    ->createQuery('SELECT b.status, b.ownerBooking, b.ownerConfirm, b.price
            	   				   FROM BookingsBookingBundle:Booking b
            	   				   WHERE b.id = ' . $bookingID . " AND b.status = 1 AND b.ownerBooking = 1 AND b.ownerConfirm = 1 AND b.price = -1")
            		->getResult();

        return (count($result) == 1);
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

            $bookingsAffected = $this->getEntityManager()
                ->createQuery('SELECT d.bookingID FROM BookingsBookingBundle:DisponibilityBooking d
                               WHERE d.date >= ' . $from . " AND d.date < " . $to . " GROUP BY d.bookingID")->getResult();

            if(!empty($bookingsAffected)){

                $arrayBookings = array();
                foreach($bookingsAffected as $booking){
                    $arrayBookings[] = $booking['bookingID'];
                }

                $bookings = $this->getEntityManager()
                    ->createQuery('SELECT b.id, b.startDate, b.endDate, b.ownerBooking, b.ownerConfirm
                                   FROM BookingsBookingBundle:Booking b
                                   WHERE b.activityID IN (' . implode(',', $arrayProperties) . ')
                                   AND b.id IN (' . implode(',', $arrayBookings) .')')->getResult();

                foreach ($bookings as $oneBooking) {
                    $aux['bookingID']       = $oneBooking['id'];
                    $aux['from']            = substr($oneBooking['startDate'], 6, 2);
                    $aux['hour']            = substr($oneBooking['startDate'], 8, 2);
                    $monthStart             = substr($oneBooking['startDate'], 4, 2);
                    $monthEnd               = substr($oneBooking['endDate'], 4, 2);
                    $aux['month']           = $monthStart;
                    $aux['year']            = substr($oneBooking['startDate'], 0, 4);
                    $aux['to']              = ($monthEnd == $monthStart) ? substr($oneBooking['endDate'], 6, 2) : 50;
                    $aux['toDay']           = substr($oneBooking['endDate'], 6, 2);
                    $aux['toMonth']         = substr($oneBooking['endDate'], 4, 2);
                    $aux['toYear']          = substr($oneBooking['endDate'], 0, 4);
                    $aux['toHour']          = substr($oneBooking['endDate'], 8, 4);
                    $aux['ownerBooking']    = $oneBooking['ownerBooking'];
                    $aux['ownerConfirm']    = $oneBooking['ownerConfirm'];
                    $results[] = $aux;
                }
            }
        }

      return $results;
    }

    function getAllBookingsInPeriod($from, $to, $arrayProperties){
        $results = array();

        if(!empty($arrayProperties)) {
            $bookings = $this->getEntityManager()
                ->createQuery('SELECT b.id, b.startDate, b.endDate
                                        FROM BookingsBookingBundle:Booking b
                                        WHERE b.activityID IN (' . implode(',', $arrayProperties) . ')
                                        AND b.startDate >= ' . $from . '
                                        AND b.startDate < ' . $to)
                ->getResult();

            foreach ($bookings as $oneBooking) {
                $aux['bookingID'] = $oneBooking['id'];
                $aux['year'] = substr($oneBooking['startDate'], 0, 4);
                $aux['month'] = substr($oneBooking['startDate'], 4, 2);
                $aux['from'] = substr($oneBooking['startDate'], 6, 2);
                $aux['hour'] = substr($oneBooking['startDate'], 8, 2);
                $monthStart = substr($oneBooking['startDate'], 4, 2);
                $monthEnd = substr($oneBooking['endDate'], 4, 2);
                $aux['to'] = ($monthEnd == $monthStart) ? substr($oneBooking['endDate'], 6, 2) : 50;
                $results[] = $aux;
            }
        }

      return $results;
    }
}
