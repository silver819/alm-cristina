<?php

namespace Bookings\BookingBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Bookings\BookingBundle\Entity\Booking;
use Bookings\BookingBundle\Entity\DisponibilityBooking;

class ConsultBookingsController extends Controller
{
	public function consultBookingsAction(Request $request)
	{
		if(!$this->get('security.context')->isGranted('ROLE_USER')) {
			throw new AccessDeniedException();
		}

        $arrayProperties    = array();
        $allOwners          = array();

        if($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')){
            $allProperties = $this->getDoctrine()
                               ->getRepository('ReservableActivityBundle:Activity')
                               ->findAll();

            foreach($allProperties as $oneResult){$arrayProperties[] = $oneResult->getId();}

            $resultOwners = $this->getDoctrine()
                                 ->getManager()
                                 ->createQuery('SELECT u.email, u.id
                                                FROM ReservableActivityBundle:Activity a
                                                INNER JOIN UserUserBundle:Users u 
                                                WHERE u.id = a.ownerID
                                                GROUP BY u.id')
                                 ->getResult();

            foreach($resultOwners as $oneResult){
                $allOwners[$oneResult['id']]['email'] = $oneResult['email'];
                $allOwners[$oneResult['id']]['ownerID'] = $oneResult['id'];
            }
        }
        else{
    		$ownerID = $this->get('security.context')->getToken()->getUser()->getId();

    		$ownerProperties = $this->getDoctrine()
                               ->getRepository('ReservableActivityBundle:Activity')
                               ->findAllByOwnerID($ownerID);
            
            foreach($ownerProperties as $oneResult){$arrayProperties[] = $oneResult->getId();}
        }

        $results = array();

        if(!empty($arrayProperties)){

            if($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') && isset($_POST['reservationID']) && !empty($_POST['reservationID'])){
                $allBookings = $this->getDoctrine()
                                    ->getRepository('BookingsBookingBundle:Booking')
                                    ->getBookingID($_POST['reservationID']);
            }
            else{
                $allBookings = $this->getDoctrine()
                                    ->getRepository('BookingsBookingBundle:Booking')
                                    ->getBookingsFromProperties($arrayProperties);
            }

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

                $aux['ownerID']         = $propertyData->getOwnerID();
                $aux['ownerEmail']      = $this->getDoctrine()
                                               ->getRepository('UserUserBundle:Users')
                                               ->getEmail($propertyData->getOwnerID());

                $aux['calendar']        = $this->showCalendar($aux['startDate'], $aux['endDate'], $request->getLocale());

                $results[] = $aux;
            }
        }

		return $this->render('BookingsBookingBundle:Consult:see-bookings.html.twig', 
			array('bookings' => $results, 'allOwners' => $allOwners));
	}

    public function historyBookingsAction(Request $request){
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

            $aux['calendar']        = $this->showCalendar($aux['startDate'], $aux['endDate'], $request->getLocale());

            $results[] = $aux;
        }

        return $this->render('BookingsBookingBundle:Consult:history-bookings.html.twig', 
            array('bookings' => $results));
    }

    public function calendarBookingsAction(Request $request){
/*
$client = new \Google_Client();
$client->setApplicationName("Almacen");

$auth = new \Google_Auth_AppIdentity($client);

$token = $auth->authenticateForScope(\Google_Service_Storage::DEVSTORAGE_READ_ONLY);
ladybug_dump($auth);die();
if (!$token) {
  die("Could not authenticate to AppIdentity service");
}
$client->setAuth($auth);
$service = new Google_Service_Storage($client);
$results = $service->buckets->listBuckets(str_replace("s~", "", $_SERVER['APPLICATION_ID']));
echo "<h3>Results Of Call:</h3>";
echo "<pre>";
var_dump($results);
echo "</pre>";
echo pageFooter(__FILE__);

echo "<br/>---------------------------------------------------------------------------<br/>";
*/

        $todayMonth = date("m");
        $todayYear  = date("Y");

        $first      = $this->showCalendar($todayYear . $todayMonth . "0100", false, $request->getLocale());
        $second     = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+1, 1, $todayYear)), false, $request->getLocale());
        $third      = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+2, 1, $todayYear)), false, $request->getLocale());
        $fourth     = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+3, 1, $todayYear)), false, $request->getLocale());
        $fifth      = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+4, 1, $todayYear)), false, $request->getLocale());
        $sixth      = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+5, 1, $todayYear)), false, $request->getLocale());
        $seventh    = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+6, 1, $todayYear)), false, $request->getLocale());
        $eighth     = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+7, 1, $todayYear)), false, $request->getLocale());
        $ninth      = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+8, 1, $todayYear)), false, $request->getLocale());
        $tenth      = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+9, 1, $todayYear)), false, $request->getLocale());
        $eleventh   = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+10, 1, $todayYear)), false, $request->getLocale());
        $twelfth    = $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+11, 1, $todayYear)), false, $request->getLocale());

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

    private function nameMonths($month, $Lang = 'es'){

        $nameMonths['es']   = array("Enero",        "Febrero",  "Marzo",        "Abril", 
                                    "Mayo",         "Junio",    "Julio",        "Agosto", 
                                    "Septiembre",   "Octubre",  "Noviembre",    "Diciembre");

        $nameMonths['en']   = array("January",      "February", "March",        "April", 
                                    "May",          "June",     "July",         "August", 
                                    "September",    "October",  "November",     "December");

        return $nameMonths[$Lang][$month-1];
    }

    private function showCalendar($since, $to = 0, $Lang = 'es'){

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

        // Seleccionamos las reservas de este mes para marcarlas en el calendario
        $ownerID        = $this->get('security.context')->getToken()->getUser()->getId();
        $fromThisDate   = $SDyear . $SDmonth . '0100';
        $toThisDate     = date('Ymd', mktime(0, 0, 0, $SDmonth + 1, 1, $SDyear)) . '00';
        
        $ownerProperties = $this->getDoctrine()
                           ->getRepository('ReservableActivityBundle:Activity')
                           ->findAllByOwnerID($ownerID);

        $arrayProperties = array();
        foreach($ownerProperties as $oneResult){$arrayProperties[] = $oneResult->getId();}

        $bookings       = $this->getDoctrine()
                               ->getRepository('BookingsBookingBundle:Booking')
                               ->getBookingsInPeriod($fromThisDate, $toThisDate, $arrayProperties);

        $stringCalendar = '';
        
        // Tabla general
        $stringCalendar .= '<table class="tablacalendario" cellspacing="3" cellpadding="2" border="0">';
        $stringCalendar .= '<tr><td colspan="7">';

        // Cabecera
        $stringCalendar .= '<table class="titleCalendar"><tr><td>' . $this->nameMonths($month, $Lang) . " " . $year . '</td></tr></table>';

        $stringCalendar .= '</td></tr>';

        $nameDayShort = array(
                            'es' => array('L','M','X','J','V','S','D'),
                            'en' => array('M','T','W','T','F','S','S')
                        );

        $stringCalendar .= '<tr>
                                <td class="dayCalendar"><span>' . $nameDayShort[$Lang][0] . '</span></td>
                                <td class="dayCalendar"><span>' . $nameDayShort[$Lang][1] . '</span></td>
                                <td class="dayCalendar"><span>' . $nameDayShort[$Lang][2] . '</span></td>
                                <td class="dayCalendar"><span>' . $nameDayShort[$Lang][3] . '</span></td>
                                <td class="dayCalendar"><span>' . $nameDayShort[$Lang][4] . '</span></td>
                                <td class="dayCalendar"><span>' . $nameDayShort[$Lang][5] . '</span></td>
                                <td class="dayCalendar"><span>' . $nameDayShort[$Lang][6] . '</span></td>
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
            else{
                $printDay = true;
                foreach($bookings as $oneBooking){
                    if($oneBooking['from'] <= $currentDay && $currentDay < $oneBooking['to']){
                        $printDay = false;
                        $stringCalendar .= '<td><span title="' . $oneBooking['bookingID'] . '" class="bookedDay">' . $currentDay . '</span></td>';
                    }
                }

                if($printDay) $stringCalendar .= '<td><span>' . $currentDay . '</span></td>';
            }

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