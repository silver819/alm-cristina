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

            $aux['calendar']        = $this->showCalendar($aux['startDateMonth'], $aux['startDateYear']);

            $results[] = $aux;
        }

		return $this->render('BookingsBookingBundle:Consult:see-bookings.html.twig', 
			array('bookings' => $results));
	}

    private function numDayOfWeek($day,$month,$year){
        $numDayWeek = date('w', mktime(0,0,0,$month,$day,$year));

        if($numDayWeek == 0)   $numDayWeek = 6;
        else                   $numDayWeek--;

        return $numDayWeek;
    }

    private function lastDayOfMonth($month,$year){
        $lastDay = 28;

        while (checkdate($month,$lastDay,$year))    $lastDay++;

        $lastDay--;

        return $lastDay;
    }

    private function nameMonths($month){

        $nameMonths = array("Enero",        "Febrero",  "Marzo",        "Abril", 
                            "Mayo",         "Junio",    "Julio",        "Agosto", 
                            "Septiembre",   "Octubre",  "Noviembre",    "Diciembre");

        return $nameMonths[$month-1];
    }

    private function showCalendar($month,$year){
        $stringCalendar = '';
        
        // Tabla general
        $stringCalendar .= '<table class="tablacalendario" cellspacing="3" cellpadding="2" border="0">';
        $stringCalendar .= '<tr><td colspan="7">';

        // Cabecera
        $stringCalendar .= '<table class="titleCalendar"><tr><td>' . $this->nameMonths($month) . " " . $year . '</td></tr></table>';

        $stringCalendar .= '</td></tr>';
        $stringCalendar .= '<tr>
                                <td class="dayCalendar"><span>L</span></td>
                                <td class="dayCalendar"><span>M</span></td>
                                <td class="dayCalendar"><span>X</span></td>
                                <td class="dayCalendar"><span>J</span></td>
                                <td class="dayCalendar"><span>V</span></td>
                                <td class="dayCalendar"><span>S</span></td>
                                <td class="dayCalendar"><span>D</span></td>
                            </tr>';
        
        $currentDay         = 1;
        $numDayWeek         = $this->numDayOfWeek(1,$month,$year);
        $lastDayOfMonth     = $this->lastDayOfMonth($month,$year);
        
        // Primera fila
        $stringCalendar .= "<tr>";
        for ($i=0;$i<7;$i++){
            if ($i < $numDayWeek){
                $stringCalendar .= '<td><span></span></td>';
            } else {
                $stringCalendar .= '<td><span>' . $currentDay . '</span></td>';
                $currentDay++;
            }
        }
        $stringCalendar .= "</tr>";
        
        // Resto de días
        $numDayWeek = 0;
        while ($currentDay <= $lastDayOfMonth){
            if ($numDayWeek == 0)   $stringCalendar .= "<tr>";

            $stringCalendar .= '<td><span>' . $currentDay . '</span></td>';
            $currentDay++;
            $numDayWeek++;

            if ($numDayWeek == 7)   {$numDayWeek = 0;$stringCalendar .= "</tr>";}
        }
        
        // Completo la tabla con días vacíos
        for ($i=$numDayWeek;$i<7;$i++)  $stringCalendar .= '<td><span></span></td>';
        
        $stringCalendar .= "</tr>";
        $stringCalendar .= "</table>";

        return $stringCalendar;
    }
}