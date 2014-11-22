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

            $aux['bookingID']       = $oneBooking->getId();
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

            $aux['calendar']        = $this->showCalendar($aux['startDate'], $aux['endDate']);

            $results[] = $aux;
        }

		return $this->render('BookingsBookingBundle:Consult:see-bookings.html.twig', 
			array('bookings' => $results));
	}

    public function historyBookingsAction(){
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
                            ->getBookingsFromPropertiesHistory($arrayProperties);

        $results = array();
        foreach($allBookings as $oneBooking){
            $propertyData   = $this->getDoctrine()
                                ->getRepository('ReservableActivityBundle:Activity')
                                ->findByPropertyID($oneBooking->getActivityID());

            $clientData     = $this->getDoctrine()
                                ->getRepository('UserUserBundle:Users')
                                ->getUserByUserID($oneBooking->getClientID());

            $aux['bookingID']       = $oneBooking->getId();
            $aux['propertyID']      = $oneBooking->getActivityID();
            $aux['clientID']        = $oneBooking->getClientID();
            $aux['price']           = $oneBooking->getPrice();
            $aux['ownerConfirm']    = $oneBooking->getOwnerConfirm();
            $aux['startDate']       = $oneBooking->getStartDate();
            $aux['startDateDay']    = substr($oneBooking->getStartDate(), 6, 2);
            $aux['startDateMonth']  = substr($oneBooking->getStartDate(), 4, 2);
            $aux['startDateYear']   = substr($oneBooking->getStartDate(), 0, 4);
            $aux['startDateHour']   = substr($oneBooking->getStartDate(), 8, 2);
            $aux['endDate']         = $oneBooking->getEndDate();
            $aux['endDateDay']      = substr($oneBooking->getEndDate(), 6, 2);
            $aux['endDateMonth']    = substr($oneBooking->getEndDate(), 4, 2);
            $aux['endDateYear']     = substr($oneBooking->getEndDate(), 0, 4);
            $aux['endDateHour']     = substr($oneBooking->getEndDate(), 8, 2);

            $aux['type']            = $propertyData->getTypeRent();
            $aux['propertyName']    = $propertyData->getName();

            $aux['clientName']      = $clientData->getName();
            $aux['clientSurname']   = $clientData->getSurname();
            $aux['clientEmail']     = $clientData->getEmail();

            $aux['calendar']        = $this->showCalendar($aux['startDate'], $aux['endDate']);

            $results[] = $aux;
        }

        return $this->render('BookingsBookingBundle:Consult:history-bookings.html.twig', 
            array('bookings' => $results));
    }

    public function calendarBookingsAction(){

        $todayMonth = date("m");
        $todayYear  = date("Y");
        
        $first      = $this->showCalendar($todayYear . $todayMonth . "0100");
        $second     = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+1, 1, $todayYear)));
        $third      = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+2, 1, $todayYear)));
        $fourth     = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+3, 1, $todayYear)));
        $fifth      = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+4, 1, $todayYear)));
        $sixth      = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+5, 1, $todayYear)));
        $seventh    = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+6, 1, $todayYear)));
        $eighth     = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+7, 1, $todayYear)));
        $ninth      = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+8, 1, $todayYear)));
        $tenth      = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+9, 1, $todayYear)));
        $eleventh   = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+10, 1, $todayYear)));
        $twelfth    = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+11, 1, $todayYear)));

        return $this->render('BookingsBookingBundle:Consult:calendar-bookings.html.twig',
            array(  'first' => $first,      'second' => $second,        'third' => $third, 
                    'fourth' => $fourth,    'fifth' => $fifth,          'sixth' => $sixth,
                    'seventh' => $seventh,  'eighth' => $eighth,        'ninth' => $ninth,
                    'tenth' => $tenth,      'eleventh' => $eleventh,    'twelfth' => $twelfth));
    }

    public function acceptBookingAction(){
        if($_POST['bookingID']){
            if($this->getDoctrine()
                    ->getRepository('BookingsBookingBundle:Booking')
                    ->acceptBooking($_POST['bookingID'])){
                
                return $this->redirect('../consult-bookings');
            }
            else{
                die("No se ha podido aceptar la reserva " . $_POST['bookingID']);
            }
        }
        else{
            die("No se ha encontrado la reserva " . $_POST['bookingID']);
        }
    }

    public function cancelBookingAction(){
        if($_POST['bookingID']){
            if($this->getDoctrine()
                    ->getRepository('BookingsBookingBundle:Booking')
                    ->cancelBooking($_POST['bookingID'])){
                
                return $this->redirect('../consult-bookings');
            }
            else{
                die("No se ha podido aceptar la reserva " . $_POST['bookingID']);
            }
        }
        else{
            die("No se ha encontrado la reserva " . $_POST['bookingID']);
        }
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

    private function showCalendar($since, $to = 0){

        $showPeriod = false;
        $SDday      = substr($since, 6, 2);
        $SDmonth    = substr($since, 4, 2);
        $SDyear     = substr($since, 0, 4);

        if($to != 0){
            $showPeriod = true;
            $EDday      = substr($to, 6, 2);
            $EDmonth    = substr($to, 4, 2);
            $EDyear     = substr($to, 0, 4);
        }

         $month = $SDmonth;
         $year  = $SDyear;

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
                if($showPeriod && $SDday <= $currentDay && $currentDay < $EDday)
                    $stringCalendar .= '<td><span class="selectedDay">' . $currentDay . '</span></td>';
                else
                    $stringCalendar .= '<td><span>' . $currentDay . '</span></td>';
                $currentDay++;
            }
        }
        $stringCalendar .= "</tr>";
        
        // Resto de días
        $numDayWeek = 0;
        while ($currentDay <= $lastDayOfMonth){
            if ($numDayWeek == 0)   $stringCalendar .= "<tr>";

            if($showPeriod && $SDday <= $currentDay && $currentDay < $EDday)
                $stringCalendar .= '<td><span class="selectedDay">' . $currentDay . '</span></td>';
            else
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