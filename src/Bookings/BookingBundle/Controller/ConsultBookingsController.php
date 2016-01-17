<?php

namespace Bookings\BookingBundle\Controller;

use Reservable\ActivityBundle\Entity\ActivityToIcal;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Bookings\BookingBundle\Entity\Booking;
use Bookings\BookingBundle\Entity\DisponibilityBooking;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class ConsultBookingsController extends Controller
{
	public function consultBookingsAction(Request $request)
	{
		if(!$this->get('security.context')->isGranted('ROLE_USER')) {
			throw new AccessDeniedException();
		}

        $arrayProperties    = array();
        $allOwners          = array();
        $results            = array();

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
        else if($this->get('security.context')->isGranted('ROLE_ADMIN')){
    		$ownerID = $this->get('security.context')->getToken()->getUser()->getId();

    		$ownerProperties = $this->getDoctrine()
                               ->getRepository('ReservableActivityBundle:Activity')
                               ->findAllByOwnerID($ownerID);

            foreach($ownerProperties as $oneResult){$arrayProperties[] = $oneResult->getId();}
        }
        else{
            $userID         = $this->get('security.context')->getToken()->getUser()->getId();
            $userName       = $this->get('security.context')->getToken()->getUser()->getName();
            $userSurname    = $this->get('security.context')->getToken()->getUser()->getSurname();
            $userEmail      = $this->get('security.context')->getToken()->getUser()->getEmail();

            $userBookings   = $this->getDoctrine()
                                   ->getManager()
                                    ->createQuery('SELECT b.id, b.activityID, b.startDate, b.endDate, b.price, a.typeRent, a.name, a.ownerID
                                               FROM BookingsBookingBundle:Booking b
                                               JOIN ReservableActivityBundle:Activity a
                                               WHERE b.activityID = a.id
                                               AND b.ownerConfirm != -1
                                               AND b.startDate >= ' . date('Ymd') . '
                                               AND b.clientID = ' . $userID)
                                    ->getResult();
            foreach($userBookings as $oneBooking){
                $aux                    = array();

                $aux['bookingID']       = $oneBooking['id'];
                $aux['propertyID'] 		= $oneBooking['activityID'];
                $aux['clientID'] 		= $userID;
                $aux['price'] 			= $oneBooking['price'];
                $aux['startDate'] 		= $oneBooking['startDate'];
                $aux['startDateDay'] 	= substr($oneBooking['startDate'], 6, 2);
                $aux['startDateMonth']	= substr($oneBooking['startDate'], 4, 2);
                $aux['startDateYear'] 	= substr($oneBooking['startDate'], 0, 4);
                $aux['startDateHour'] 	= substr($oneBooking['startDate'], 8, 2);
                $aux['endDate'] 		= $oneBooking['endDate'];
                $aux['endDateDay'] 		= substr($oneBooking['endDate'], 6, 2);
                $aux['endDateMonth']	= substr($oneBooking['endDate'], 4, 2);
                $aux['endDateYear'] 	= substr($oneBooking['endDate'], 0, 4);
                $aux['endDateHour'] 	= substr($oneBooking['endDate'], 8, 2);

                $aux['type'] 			= $oneBooking['typeRent'];
                $aux['propertyName'] 	= $oneBooking['name'];

                $aux['clientName'] 		= $userName;
                $aux['clientSurname'] 	= $userSurname;
                $aux['clientEmail'] 	= $userEmail;

                $aux['ownerID']         = $oneBooking['ownerID'];
                $aux['ownerEmail']      = $this->getDoctrine()
                                            ->getRepository('UserUserBundle:Users')
                                            ->getEmail($oneBooking['ownerID']);

                $aux['calendar']        = $this->showCalendar($aux['startDate'], $aux['endDate'], $request->getLocale(), $oneBooking['id']);

                $results[] = $aux;
            }
        }

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

                if($propertyData->getTypeRent() == 'day') {
                    $aux['calendar'] = $this->showCalendar($aux['startDate'], $aux['endDate'], $request->getLocale(), $propertyData->getId());
                }
                else {
                    $aux['calendar'] = $this->showCalendarByWeek($aux['startDate'], false, $request->getLocale(), $propertyData->getId());
                }

                $results[] = $aux;
            }
        }

		return $this->render('BookingsBookingBundle:Consult:see-bookings.html.twig', 
			array('bookings' => $results, 'allOwners' => $allOwners));
	}

    public function consultclientBookingsAction(Request $request){

        $results = array();

        $userID         = $this->get('security.context')->getToken()->getUser()->getId();
        $userName       = $this->get('security.context')->getToken()->getUser()->getName();
        $userSurname    = $this->get('security.context')->getToken()->getUser()->getSurname();
        $userEmail      = $this->get('security.context')->getToken()->getUser()->getEmail();

        $userBookings   = $this->getDoctrine()
            ->getManager()
            ->createQuery('SELECT b.id, b.activityID, b.startDate, b.endDate, b.price, a.typeRent, a.name, a.ownerID
                                               FROM BookingsBookingBundle:Booking b
                                               JOIN ReservableActivityBundle:Activity a
                                               WHERE b.activityID = a.id
                                               AND b.ownerConfirm != -1
                                               AND b.startDate >= ' . date('Ymd') . '
                                               AND b.clientID = ' . $userID)
            ->getResult();

        foreach($userBookings as $oneBooking){
            $aux                    = array();

            $aux['bookingID']       = $oneBooking['id'];
            $aux['propertyID'] 		= $oneBooking['activityID'];
            $aux['clientID'] 		= $userID;
            $aux['price'] 			= $oneBooking['price'];
            $aux['startDate'] 		= $oneBooking['startDate'];
            $aux['startDateDay'] 	= substr($oneBooking['startDate'], 6, 2);
            $aux['startDateMonth']	= substr($oneBooking['startDate'], 4, 2);
            $aux['startDateYear'] 	= substr($oneBooking['startDate'], 0, 4);
            $aux['startDateHour'] 	= substr($oneBooking['startDate'], 8, 2);
            $aux['endDate'] 		= $oneBooking['endDate'];
            $aux['endDateDay'] 		= substr($oneBooking['endDate'], 6, 2);
            $aux['endDateMonth']	= substr($oneBooking['endDate'], 4, 2);
            $aux['endDateYear'] 	= substr($oneBooking['endDate'], 0, 4);
            $aux['endDateHour'] 	= substr($oneBooking['endDate'], 8, 2);

            $aux['type'] 			= $oneBooking['typeRent'];
            $aux['propertyName'] 	= $oneBooking['name'];

            $aux['clientName'] 		= $userName;
            $aux['clientSurname'] 	= $userSurname;
            $aux['clientEmail'] 	= $userEmail;

            $aux['ownerID']         = $oneBooking['ownerID'];
            $aux['ownerEmail']      = $this->getDoctrine()
                ->getRepository('UserUserBundle:Users')
                ->getEmail($oneBooking['ownerID']);

            $aux['calendar']        = $this->showCalendar($aux['startDate'], $aux['endDate'], $request->getLocale(), $oneBooking['activityID']);

            $results[] = $aux;
        }

        return $this->render('BookingsBookingBundle:Consult:see-bookings.html.twig',
            array('bookings' => $results, 'allOwners' => array()));
    }

    public function historyBookingsAction(Request $request){
        if(!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        $ownerID = $this->get('security.context')->getToken()->getUser()->getId();

        if(!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $ownerProperties = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:Activity')
                ->findAllByOwnerID($ownerID);
        }
        else{
            $ownerProperties = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:Activity')
                ->findAll();
        }

        $arrayProperties = array();
        foreach($ownerProperties as $oneResult){$arrayProperties[] = $oneResult->getId();}

        $allBookings = $this->getDoctrine()
                            ->getRepository('BookingsBookingBundle:Booking')
                            ->getBookingsFromPropertiesHistory($arrayProperties);

        $results = array();
        if(!empty($allBookings)) {
            foreach ($allBookings as $oneBooking) {
                $propertyData = $this->getDoctrine()
                    ->getRepository('ReservableActivityBundle:Activity')
                    ->findByPropertyID($oneBooking->getActivityID());

                $clientData = $this->getDoctrine()
                    ->getRepository('UserUserBundle:Users')
                    ->getUserByUserID($oneBooking->getClientID());

                $aux['bookingID'] = $oneBooking->getId();
                $aux['propertyID'] = $oneBooking->getActivityID();
                $aux['clientID'] = $oneBooking->getClientID();
                $aux['price'] = $oneBooking->getPrice();
                $aux['ownerConfirm'] = $oneBooking->getOwnerConfirm();
                $aux['startDate'] = $oneBooking->getStartDate();
                $aux['startDateDay'] = substr($oneBooking->getStartDate(), 6, 2);
                $aux['startDateMonth'] = substr($oneBooking->getStartDate(), 4, 2);
                $aux['startDateYear'] = substr($oneBooking->getStartDate(), 0, 4);
                $aux['startDateHour'] = substr($oneBooking->getStartDate(), 8, 2);
                $aux['endDate'] = $oneBooking->getEndDate();
                $aux['endDateDay'] = substr($oneBooking->getEndDate(), 6, 2);
                $aux['endDateMonth'] = substr($oneBooking->getEndDate(), 4, 2);
                $aux['endDateYear'] = substr($oneBooking->getEndDate(), 0, 4);
                $aux['endDateHour'] = substr($oneBooking->getEndDate(), 8, 2);

                $aux['type'] = $propertyData->getTypeRent();
                $aux['propertyName'] = $propertyData->getName();

                $aux['clientName'] = $clientData->getName();
                $aux['clientSurname'] = $clientData->getSurname();
                $aux['clientEmail'] = $clientData->getEmail();

                $aux['calendar'] = $this->showCalendar($aux['startDate'], $aux['endDate'], $request->getLocale(), $propertyData->getId());

                $results[] = $aux;
            }
        }

        return $this->render('BookingsBookingBundle:Consult:history-bookings.html.twig', 
            array('bookings' => $results));
    }

    public function historyBookingsUserAction(Request $request){

        $clientID = $this->get('security.context')->getToken()->getUser()->getId();

        $allBookings = $this->getDoctrine()
            ->getRepository('BookingsBookingBundle:Booking')
            ->findAllBookingsByClientID($clientID);

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

            $aux['calendar']        = $this->showCalendar($aux['startDate'], $aux['endDate'], $request->getLocale(), $propertyData->getId());

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
        $selector   = array();
        $calendar   = '';
        $todayMonth = date("m");
        $todayYear  = date("Y");

        if($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            $properties = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:Activity')
                ->findAll();
        }
        else {
            $properties = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:Activity')
                ->findAllByOwnerID($this->get('security.context')->getToken()->getUser()->getId());
        }

        foreach($properties as $oneProperty){
            $aux         = array();
            $aux['name'] = $oneProperty->getName();
            $aux['id']   = $oneProperty->getId();
            $aux['type'] = $oneProperty->getTypeRent();

            $selector[] = $aux;
        }

        if(isset($selector[0]['type'])) {
            if ($selector[0]['type'] == 'hour') {
                $calendar .= '<div class="row clearfix"><div class="col-md-12 column">';
                $calendar .= $this->showCalendarByDay($todayYear . $todayMonth . "0100", false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div><div class="col-md-12 column"><br/>';
                $calendar .= $this->showCalendarByDay(date("Ymd", mktime(0, 0, 0, $todayMonth + 1, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div><div class="col-md-12 column"><br/>';
                $calendar .= $this->showCalendarByDay(date("Ymd", mktime(0, 0, 0, $todayMonth + 2, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div></div>';
            } else {
                $calendar .= '<div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar($todayYear . $todayMonth . "0100", false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 1, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 2, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 3, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 4, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 5, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 6, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 7, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 8, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 9, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 10, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 11, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
                $calendar .= '</div></div>';
            }
        }

        $importedCalendar = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:ActivityToIcal')
            ->getAllIcsFromActivityFormatted($selector[0]['id']);

        // Creamos ics
        $fromThisDate   = date('Ymd') . '00';
        $toThisDate     = date('Ymd', strtotime('+ 1 year')) . '00';

        foreach($properties as $key => $oneProperty){

            $selector[$key]['bookings'] = $this->getDoctrine()
                ->getRepository('BookingsBookingBundle:Booking')
                ->getBookingsInPeriod($fromThisDate, $toThisDate, array($oneProperty->getId()));
        }

        foreach($selector as $key => $data){
            $selector[$key]['icalPath'] = $this->getIcs($data);
        }

        //ldd($selector);

        return $this->render('BookingsBookingBundle:Consult:calendar-bookings.html.twig',
            array('calendar'     => $calendar, 'importedCalendar' => $importedCalendar, 'selector'  => $selector));
    }

    public function calculateCalendarAction(){
        $request    = $this->getRequest();

        $activityID = $request->request->get('activityID');

        $nameLodging = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:Activity')
            ->findNameByActivityID($activityID);

        $typeLodging = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:Activity')
            ->findTypeByActivityID($activityID);

        $calendar   = '';
        $todayMonth = date("m");
        $todayYear  = date("Y");

        if($typeLodging == 'hour'){
            $calendar .= '<div class="row clearfix"><div class="col-md-12 column">';
            $calendar .= $this->showCalendarByDay($todayYear . $todayMonth . "0100", false, $request->getLocale(), $activityID);
            $calendar .= '</div><div class="col-md-12 column"><br/>';
            $calendar .= $this->showCalendarByDay(date("Ymd", mktime(0, 0, 0, $todayMonth + 1, 1, $todayYear)), false, $request->getLocale(), $activityID);
            $calendar .= '</div><div class="col-md-12 column"><br/>';
            $calendar .= $this->showCalendarByDay(date("Ymd", mktime(0, 0, 0, $todayMonth + 2, 1, $todayYear)), false, $request->getLocale(), $activityID);
            $calendar .= '</div></div>';

        }
        else{
            $calendar .= '<div class="row clearfix"><div class="col-md-4 column">';
            $calendar .= $this->showCalendar($todayYear . $todayMonth . "0100", false, $request->getLocale(), $activityID);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 1, 1, $todayYear)), false, $request->getLocale(), $activityID);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 2, 1, $todayYear)), false, $request->getLocale(), $activityID);
            $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 3, 1, $todayYear)), false, $request->getLocale(), $activityID);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 4, 1, $todayYear)), false, $request->getLocale(), $activityID);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 5, 1, $todayYear)), false, $request->getLocale(), $activityID);
            $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 6, 1, $todayYear)), false, $request->getLocale(), $activityID);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 7, 1, $todayYear)), false, $request->getLocale(), $activityID);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 8, 1, $todayYear)), false, $request->getLocale(), $activityID);
            $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 9, 1, $todayYear)), false, $request->getLocale(), $activityID);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 10, 1, $todayYear)), false, $request->getLocale(), $activityID);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 11, 1, $todayYear)), false, $request->getLocale(), $activityID);
            $calendar .= '</div></div>';
        }

        $importedCalendar = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:ActivityToIcal')
            ->getAllIcsFromActivityFormatted($activityID);

        return new JsonResponse(array(
            'calendar'          => $calendar,
            'nameLodging'       => $nameLodging,
            'downloadICS'       => '/icals/propID' . $activityID . '.ics',
            'importICS'         => $activityID,
            'importedCalendar'  => $importedCalendar)
        );
    }

    public function importCalendarFromURIAction(Request $request){

        $return = array();

        //$_POST['propID'] = 1;
        //$_POST['pathICS'] = "https://calendar.google.com/calendar/ical/3ikhnokttve640jvcmgstn20dk%40group.calendar.google.com/public/basic.ics";

        if(isset($_POST['propID']) && isset($_POST['pathICS']) && !empty($_POST['pathICS']) && !empty($_POST['propID'])) {

            // Comprobamos que no existe el calendario
            $icalFounded    = $this->getDoctrine()->getRepository('ReservableActivityBundle:ActivityToIcal')->findOneBy(array('activityID' => $_POST['propID'], 'icalUrl' => $_POST['pathICS']));
            if(is_object($icalFounded)){
                return new JsonResponse(array('reapeted' => true));
            }

            // Entity Manager
            $em = $this->getDoctrine()->getManager();

            // Guardamos la url que hemos importado
            $activityToIcal = new ActivityToIcal();
            $activityToIcal->setActivityID($_POST['propID']);
            $activityToIcal->setIcalUrl($_POST['pathICS']);
            $em->persist($activityToIcal);
            $em->flush();
            //$activityToIcal = $icalFounded;

            // Actualizamos el calendario
            $return['results'] = $this->updateIcalCalendar($_POST['pathICS'], $_POST['propID'], $activityToIcal->getId());

            $namesPart  = explode('/', $_POST['pathICS']);
            $name       = $namesPart[2];
            $thisIcal    = $this->getDoctrine()->getRepository('ReservableActivityBundle:ActivityToIcal')->findOneBy(array('activityID' => $_POST['propID'], 'icalUrl' => $_POST['pathICS']));

            $return['newRow'] = "<div class='row' id='ical-" . $thisIcal->getId() . "'><div class='col-md-8'></div><div class='col-md-2 text-center nameIcal' title='" . $_POST['pathICS'] . "'>" . $name . "</div><div class='col-md-1 text-right deleteIcal' icalID='" . $thisIcal->getId() . "'><i class='fa fa-trash-o'></i></div><div class='col-md-1 text-left refreshIcal' icalID='" . $thisIcal->getId() . "'><i class='fa fa-refresh'></i></div></div>";

            // Recargo calendario
            $selector = $this->getDoctrine()->getRepository('ReservableActivityBundle:Activity')->findOneBy(array('id' => $_POST['propID']));

            $todayYear = date('Y');
            $todayMonth = date('m');
            $calendar = '';
            if ($selector->getTypeRent() == 'hour') {
                $calendar .= '<div class="row clearfix"><div class="col-md-12 column">';
                $calendar .= $this->showCalendarByDay($todayYear . $todayMonth . "0100", false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-12 column"><br/>';
                $calendar .= $this->showCalendarByDay(date("Ymd", mktime(0, 0, 0, $todayMonth + 1, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-12 column"><br/>';
                $calendar .= $this->showCalendarByDay(date("Ymd", mktime(0, 0, 0, $todayMonth + 2, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div>';
            } else {
                $calendar .= '<div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar($todayYear . $todayMonth . "0100", false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 1, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 2, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 3, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 4, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 5, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 6, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 7, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 8, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 9, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 10, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 11, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div>';
            }
            $return['newCalendar'] = $calendar;
        }

        return new JsonResponse($return);
    }

    public function refreshIcalAction(Request $request){

        $return = array();

        //$_POST['icalToUpdate'] = 3;

        if(isset($_POST['icalToUpdate']) && $_POST['icalToUpdate']){
            $data = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:ActivityToIcal')
                ->findOneBy(array('id' => $_POST['icalToUpdate']));

            $return = $this->updateIcalCalendar($data->getIcalUrl(), $data->getActivityID(), $data->getId());
            $return['divUpdated'] = 'ical-' . $_POST['icalToUpdate'];
            $return['innerHTML'] = "<i class='fa fa-check'>";

            // Recargo calendario
            $selector = $this->getDoctrine()->getRepository('ReservableActivityBundle:Activity')->findOneBy(array('id' => $data->getActivityID()));
            $todayYear = date('Y');
            $todayMonth = date('m');
            $calendar = '';
            if ($selector->getTypeRent() == 'hour') {
                $calendar .= '<div class="row clearfix"><div class="col-md-12 column">';
                $calendar .= $this->showCalendarByDay($todayYear . $todayMonth . "0100", false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-12 column"><br/>';
                $calendar .= $this->showCalendarByDay(date("Ymd", mktime(0, 0, 0, $todayMonth + 1, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-12 column"><br/>';
                $calendar .= $this->showCalendarByDay(date("Ymd", mktime(0, 0, 0, $todayMonth + 2, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div>';
            } else {
                $calendar .= '<div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar($todayYear . $todayMonth . "0100", false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 1, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 2, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 3, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 4, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 5, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 6, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 7, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 8, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 9, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 10, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 11, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div>';
            }
            $return['newCalendar'] = $calendar;
        }

        return new JsonResponse($return);
    }

    public function deleteIcalAction(Request $request){

        $return = array();

        if(isset($_POST['icalToDelete']) && $_POST['icalToDelete']){

            $thisIcal = $this->getDoctrine()->getRepository('ReservableActivityBundle:ActivityToIcal')->findOneBy(array('id' => $_POST['icalToDelete']));
            $propID   = $thisIcal->getActivityID();

            // Primero borramos las reservas y disponibilidad
            $bookings = $this->getDoctrine()->getRepository('BookingsBookingBundle:Booking')->findBy(array('fromiCalID' => $_POST['icalToDelete']));
            foreach($bookings as $booking){
                $deleteDispo = $this->getDoctrine()->getManager()
                    ->createQuery("DELETE FROM BookingsBookingBundle:DisponibilityBooking db
                                   WHERE db.bookingID = " . $booking->getId())->getResult();
            }

            $deleteBookings = $this->getDoctrine()->getManager()
                ->createQuery("DELETE FROM BookingsBookingBundle:Booking b
                                   WHERE b.fromiCalID = " . $_POST['icalToDelete'])->getResult();

            $data = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:ActivityToIcal')
                ->deleteIcal($_POST['icalToDelete']);

            $return['divToDelete'] = 'ical-' . $_POST['icalToDelete'];

            // Recargo calendario
            $selector = $this->getDoctrine()->getRepository('ReservableActivityBundle:Activity')->findOneBy(array('id' => $propID));
            $todayYear = date('Y');
            $todayMonth = date('m');
            $calendar = '';
            if ($selector->getTypeRent() == 'hour') {
                $calendar .= '<div class="row clearfix"><div class="col-md-12 column">';
                $calendar .= $this->showCalendarByDay($todayYear . $todayMonth . "0100", false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-12 column"><br/>';
                $calendar .= $this->showCalendarByDay(date("Ymd", mktime(0, 0, 0, $todayMonth + 1, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-12 column"><br/>';
                $calendar .= $this->showCalendarByDay(date("Ymd", mktime(0, 0, 0, $todayMonth + 2, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div>';
            } else {
                $calendar .= '<div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar($todayYear . $todayMonth . "0100", false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 1, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 2, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 3, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 4, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 5, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 6, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 7, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 8, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 9, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 10, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div><div class="col-md-4 column">';
                $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth + 11, 1, $todayYear)), false, $request->getLocale(), $selector->getId());
                $calendar .= '</div></div>';
            }
            $return['newCalendar'] = $calendar;
        }

        return new JsonResponse($return);
    }

    private function deleteBookingsFromIcalID($icalID){

        // Primero borramos las reservas y disponibilidad
        $bookings = $this->getDoctrine()->getRepository('BookingsBookingBundle:Booking')->findBy(array('fromiCalID' => $icalID));
        foreach($bookings as $booking){
            $deleteDispo = $this->getDoctrine()->getManager()
                ->createQuery("DELETE FROM BookingsBookingBundle:DisponibilityBooking db
                                   WHERE db.bookingID = " . $booking->getId())->getResult();
        }

        $deleteBookings = $this->getDoctrine()->getManager()
            ->createQuery("DELETE FROM BookingsBookingBundle:Booking b
                                   WHERE b.fromiCalID = " . $icalID)->getResult();
    }

    public function updateIcalCalendar($url, $propID, $icalID){

        // Entity Manager
        $em     = $this->getDoctrine()->getManager();
        $return = array();

        // Limpiamos los eventos que tengamos guardados anteriormente para sincronizar
        $this->deleteBookingsFromIcalID($icalID);
        // Transformamos los eventos del ical en array
        $arrayEvents = $this->getEventsFromIcal($url);

        // Obtengo el objeto actividad
        $activity = $this->getDoctrine()->getRepository('ReservableActivityBundle:Activity')->findOneBy(array('id' => $propID));

        // Recorremos el array y hacemos las reservas de cada evento
        if (isset($arrayEvents['VEVENT']) && !empty($arrayEvents['VEVENT'])) {

            $today = date('Ymd');

            foreach ($arrayEvents['VEVENT'] as $event) {

                $dateStartYear = substr($event['DTSTART'], 0, 4);
                $dateStartMonth = substr($event['DTSTART'], 4, 2);
                $dateStartDay = substr($event['DTSTART'], 6, 2);
                $dateEndYear = substr($event['DTEND'], 0, 4);
                $dateEndMonth = substr($event['DTEND'], 4, 2);
                $dateEndDay = substr($event['DTEND'], 6, 2);

                if($today < (int)$dateEndYear.$dateEndMonth.$dateEndDay) {

                    if ($activity->getTypeRent() == 'hour') {
                        $dateStartHour = (int)substr($event['DTSTART'], 9, 2) + 1;
                        $dateEndHour = (int)substr($event['DTEND'], 9, 2) + 1;
                    } else {
                        $dateStartHour = '00';
                        $dateEndHour = '00';
                    }

                    $dateStart = (int)($dateStartYear . $dateStartMonth . $dateStartDay . $dateStartHour);
                    $dateEnd = (int)($dateEndYear . $dateEndMonth . $dateEndDay . $dateEndHour);

                    $arrayDays = array();
                    $currentDay = $dateStart;
                    if ($activity->getTypeRent() == 'hour') {
                        // Horas entre el inicio y el final del evento
                        while ($currentDay < $dateEnd) {
                            $arrayDays[] = (int)$currentDay;

                            $day = substr($currentDay, 6, 2);
                            $month = substr($currentDay, 4, 2);
                            $year = substr($currentDay, 0, 4);
                            $hour = substr($currentDay, 8, 2);

                            $currentDay = date('YmdH', mktime($hour + 1, 0, 0, $month, $day, $year));
                        }
                    } else {
                        // Dias entre el inicio y el final del evento
                        while ($currentDay < $dateEnd) {
                            $arrayDays[] = (int)$currentDay;

                            $day = substr($currentDay, 6, 2);
                            $month = substr($currentDay, 4, 2);
                            $year = substr($currentDay, 0, 4);

                            $currentDay = date('Ymd', mktime(0, 0, 0, $month, $day + 1, $year)) . '00';
                        }
                    }

                    if (!empty($arrayDays)) {

                        // Comprobamos disponibilidad
                        $daysOcuppated = $this->getDoctrine()->getManager()
                            ->createQuery('SELECT d.date, d.bookingID, b.id as propertyID
                                   FROM BookingsBookingBundle:Booking b
                                   INNER JOIN BookingsBookingBundle:DisponibilityBooking d
                                   WHERE b.id = d.bookingID AND b.activityID = ' . $propID . ' AND d.date IN (' . implode(',', $arrayDays) . ')')
                            ->getResult();

                        if (empty($daysOcuppated)) {
                            // Si hay disponibilidad, hacemos la reserva
                            $thisBooking = new Booking();
                            $thisBooking->setActivityID($propID)
                                ->setClientID($this->get('security.context')->getToken()->getUser()->getId())
                                ->setStartDate((string)($dateStart))
                                ->setEndDate((string)($dateEnd))
                                ->setPrice(-1)
                                ->setStatus(0)
                                ->setOwnerBooking(1)
                                ->setOwnerConfirm(1)
                                ->setFromIcalID($icalID);


                            $em->persist($thisBooking);
                            $em->flush();

                            foreach ($arrayDays as $oneDay) {

                                $oneItem = new DisponibilityBooking();
                                $oneItem->setBookingID($thisBooking->getId());
                                $oneItem->setDate($oneDay);

                                $em->persist($oneItem);
                            }

                            $return['Done'][] = array('dateStart' => $dateStart, 'dateEnd' => $dateEnd);

                        } else {
                            // Si no hay disponibilidad, comprobamos que sea un bloqueo de portal
                            $arrayCoincidences = array();
                            foreach ($daysOcuppated as $dayOcuppated) {
                                $arrayCoincidences[$dayOcuppated['bookingID']] = 1;
                            }

                            foreach ($arrayCoincidences as $coincidence) {

                                $isIcalSync = $this->getDoctrine()->getRepository('BookingsBookingBundle:Booking')->isIcalSync($coincidence);

                                if ($isIcalSync) {
                                    $return['AlreadySynced'][] = array('dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'coincidences' => $coincidence);
                                } else {
                                    $return['Error'][] = array('dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'coincidences' => $coincidence);
                                }
                            }
                        }
                    }
                }
            }
        }

        $em->flush();

        return $return;
    }

    private function getEventsFromIcal($url){

        $contents       = file_get_contents($url);
        $lines          = explode("\n", $contents);

        $todo_count     = 0;
        $event_count    = 0;
        $freebusy_count = 0;

        if (stristr($lines[0], 'BEGIN:VCALENDAR') === false) {
            return false;
        } else {
            foreach ($lines as $line) {
                $line = rtrim($line); // Trim trailing whitespace
                $add  = $this->keyValueFromString($line);
                if ($add === false) {
                    $this->addCalendarComponentWithKeyAndValue($component, false, $value, $last_keyword, $todo_count, $event_count, $freebusy_count, $cal);
                    continue;
                }
                $keyword = $add[0];
                $values = $add[1]; // Could be an array containing multiple values
                if (!is_array($values)) {
                    if (!empty($values)) {
                        $values = array($values); // Make an array as not already
                        $blank_array = array(); // Empty placeholder array
                        array_push($values, $blank_array);
                    } else {
                        $values = array(); // Use blank array to ignore this line
                    }
                } else if(empty($values[0])) {
                    $values = array(); // Use blank array to ignore this line
                }
                $values = array_reverse($values); // Reverse so that our array of properties is processed first

                foreach ($values as $value) {
                    switch ($line) {
                        // http://www.kanzaki.com/docs/ical/vtodo.html
                        case 'BEGIN:VTODO':
                            $todo_count++;
                            $component = 'VTODO';
                            break;
                        // http://www.kanzaki.com/docs/ical/vevent.html
                        case 'BEGIN:VEVENT':
                            if (!is_array($value)) {
                                $event_count++;
                            }
                            $component = 'VEVENT';
                            break;
                        // http://www.kanzaki.com/docs/ical/vfreebusy.html
                        case 'BEGIN:VFREEBUSY':
                            $freebusy_count++;
                            $component = 'VFREEBUSY';
                            break;
                        // All other special strings
                        case 'BEGIN:VCALENDAR':
                        case 'BEGIN:DAYLIGHT':
                            // http://www.kanzaki.com/docs/ical/vtimezone.html
                        case 'BEGIN:VTIMEZONE':
                        case 'BEGIN:STANDARD':
                        case 'BEGIN:VALARM':
                            $component = $value;
                            break;
                        case 'END:VALARM':
                        case 'END:VTODO': // End special text - goto VCALENDAR key
                        case 'END:VEVENT':
                        case 'END:VFREEBUSY':
                        case 'END:VCALENDAR':
                        case 'END:DAYLIGHT':
                        case 'END:VTIMEZONE':
                        case 'END:STANDARD':
                            $component = 'VCALENDAR';
                            break;
                        default:
                            $this->addCalendarComponentWithKeyAndValue($component, $keyword, $value, $last_keyword, $todo_count, $event_count, $freebusy_count, $cal);
                            break;
                    }
                }
            }
            $this->process_recurrences($cal);
            return $cal;
        }
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
                die("No se ha podido cancelar la reserva " . $_POST['bookingID']);
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

    private function showCalendar($since, $to = 0, $Lang = 'es', $propertyID){

        $daysPrinted = array();

        $showPeriod = false;
        $SDday      = substr($since, 6, 2);
        $SDmonth    = substr($since, 4, 2);
        $SDyear     = substr($since, 0, 4);

        $todayDay   = date('d');
        $todayMonth = date('m');

        if($to != 0){
            $showPeriod = true;
            $EDday      = substr($to, 6, 2);
            $EDmonth    = substr($to, 4, 2);
            $EDyear     = substr($to, 0, 4);
        }

        $month = $SDmonth;
        $year  = $SDyear;

        $fromThisDate   = $SDyear . $SDmonth . '0100';
        $toThisDate     = date('Ymd', mktime(0, 0, 0, $SDmonth + 1, 1, $SDyear)) . '00';

        $bookings       = $this->getDoctrine()
                               ->getRepository('BookingsBookingBundle:Booking')
                               ->getBookingsInPeriod($fromThisDate, $toThisDate, array($propertyID));

        $stringCalendar = '';
        
        // Tabla general
        $stringCalendar .= '<table class="table table-bordered text-center"><tbody>';

        // Cabecera
        $stringCalendar .= '<tr class="monthName"><th colspan="7">' . $this->nameMonths($month, $Lang) . ' ' . $year . '</th></tr>';

        // Días de la semana
        $nameDayShort = array('es' => array('L','M','X','J','V','S','D'), 'en' => array('M','T','W','T','F','S','S'));
        $stringCalendar .= '<tr class="weekName"><th>' . $nameDayShort[$Lang][0] . '</th><th>' . $nameDayShort[$Lang][1] . '</th><th>' . $nameDayShort[$Lang][2] . '</th><th>' . $nameDayShort[$Lang][3] . '</th><th>' . $nameDayShort[$Lang][4] . '</th><th>' . $nameDayShort[$Lang][5] . '</th><th>' . $nameDayShort[$Lang][6] . '</th></tr>';
        
        $currentDay         = 1;
        $numDayWeek         = $this->numDayOfWeek(1,$month,$year);
        $lastDayOfMonth     = $this->lastDayOfMonth($month,$year);
        
        // Primera fila
        $stringCalendar .= "<tr>";
        for ($i=0;$i<7;$i++){
            if ($i < $numDayWeek){
                $stringCalendar .= '<td class="noDay"><span></span></td>';
            } else {
                $classPassed = '';
                if($month == $todayMonth && $currentDay < $todayDay){
                    $classPassed = 'passedDay';
                }

                if($showPeriod && $SDday <= $currentDay && $currentDay < $EDday)
                    $stringCalendar .= '<td class="selectedDay"><span >' . $currentDay . '</span></td>';
                else{
                    $printDay = true;
                    foreach($bookings as $oneBooking){
                        if(
                            (
                                ($oneBooking['month'] == $oneBooking['toMonth'] && $oneBooking['from'] <= $currentDay && $currentDay < $oneBooking['toDay'])
                                || ($oneBooking['month'] != $oneBooking['toMonth'] && $oneBooking['month'] == $month && $oneBooking['from'] <= $currentDay)
                                || ($oneBooking['month'] != $oneBooking['toMonth'] && $oneBooking['toMonth'] == $month && $currentDay < $oneBooking['toDay'])
                            )
                            && (!in_array($currentDay, $daysPrinted))
                        )
                        {

                            $printDay       = false;
                            $daysPrinted[]  = $currentDay;
                            $class          = 'bookedDay';
                            if($oneBooking['ownerBooking'] && $oneBooking['ownerConfirm'])      $class = 'blockedDay';
                            if(!$oneBooking['ownerBooking'] && !$oneBooking['ownerConfirm'])    $class = 'pendingDay';

                            $stringCalendar .= '<td class="' . $class . ' ' . $classPassed . '"><span title="' . $oneBooking['bookingID'] . '" >' . $currentDay . '</span></td>';
                        }
                    }

                    if($printDay) $stringCalendar .= '<td class="' . $classPassed . '"><span>' . $currentDay . '</span></td>';
                }
                $currentDay++;
            }
        }
        $stringCalendar .= "</tr>";
        
        // Resto de días
        $numDayWeek = 0;
        while ($currentDay <= $lastDayOfMonth){
            $classPassed = '';
            if($month == $todayMonth && $currentDay < $todayDay){
                $classPassed = 'passedDay';
            }

            if ($numDayWeek == 0)   $stringCalendar .= "<tr>";

            if($showPeriod && $SDday <= $currentDay && $currentDay < $EDday)
                $stringCalendar .= '<td class="selectedDay"><span>' . $currentDay . '</span></td>';
            else{
                $printDay = true;
                foreach($bookings as $oneBooking){
                    if(
                        (
                            ($oneBooking['month'] == $oneBooking['toMonth'] && $oneBooking['from'] <= $currentDay && $currentDay < $oneBooking['toDay'])
                            || ($oneBooking['month'] != $oneBooking['toMonth'] && $oneBooking['month'] == $month && $oneBooking['from'] <= $currentDay)
                            || ($oneBooking['month'] != $oneBooking['toMonth'] && $oneBooking['toMonth'] == $month && $currentDay < $oneBooking['toDay'])
                        )
                        && (!in_array($currentDay, $daysPrinted))
                    )
                    {

                        $printDay       = false;
                        $daysPrinted[]  = $currentDay;
                        $class          = 'bookedDay';
                        if($oneBooking['ownerBooking'] && $oneBooking['ownerConfirm'])      $class = 'blockedDay';
                        if(!$oneBooking['ownerBooking'] && !$oneBooking['ownerConfirm'])    $class = 'pendingDay';

                        $stringCalendar .= '<td class="' . $class . ' ' . $classPassed . '"><span title="' . $oneBooking['bookingID'] . '" >' . $currentDay . '</span></td>';
                    }
                }

                if($printDay) $stringCalendar .= '<td class="' . $classPassed . '"><span>' . $currentDay . '</span></td>';
            }

            $currentDay++;
            $numDayWeek++;

            if ($numDayWeek == 7)   {$numDayWeek = 0;$stringCalendar .= "</tr>";}
        }
        
        // Completo la tabla con días vacíos
        for ($i=$numDayWeek;$i<7;$i++)  $stringCalendar .= '<td class="noDay"><span></span></td>';
        
        $stringCalendar .= "</tr>";
        $stringCalendar .= "</tbody></table>";

        return $stringCalendar;
    }

    private function showCalendarByDay($since, $to = 0, $Lang = 'es', $propertyID){

        $stringCalendar = "";
        $SDday          = substr($since, 6, 2);
        $SDmonth        = substr($since, 4, 2);
        $SDyear         = substr($since, 0, 4);

        $todayDay       = date('d');
        $todayMonth     = date('m');
        $todayHour      = date('H');

        $numDaysInThisMonth = cal_days_in_month(CAL_GREGORIAN, $SDmonth, $SDyear);

        if($to != 0){
            $EDday      = substr($to, 6, 2);
            $EDmonth    = substr($to, 4, 2);
            $EDyear     = substr($to, 0, 4);
        }

        $month          = $SDmonth;
        $year           = $SDyear;
        $lastDayOfMonth = $this->lastDayOfMonth($month,$year);

        $fromThisDate   = $SDyear . $SDmonth . '0100';
        $toThisDate     = date('Ymd', mktime(0, 0, 0, $SDmonth + 1, 1, $SDyear)) . '00';

        $bookings       = $this->getDoctrine()
            ->getRepository('BookingsBookingBundle:Booking')
            ->getBookingsInPeriod($fromThisDate, $toThisDate, array($propertyID));

        $stringCalendar .= '<table class="table table-bordered text-center">';
        // Cabecera
        $stringCalendar .= '<tr class="monthName"><th colspan="' . ($numDaysInThisMonth+1) . '">' . $this->nameMonths($month, $Lang) . ' ' . $year . '</th></tr><tr><td></td>';
        // Días
        for($i=1 ; $i <= $numDaysInThisMonth ; $i++) {
            $classPassed = '';
            if($month == $todayMonth && $i < $todayDay){
                $classPassed = 'passedDay';
            }

            if($i <= $lastDayOfMonth) {
                $stringCalendar .= '<th class="cabeceraCalendar borderTD weekName ' . $classPassed . '">' . $i . '</th>';
            }
            else {
                $stringCalendar .= '<th class="noAvailableDay ' . $classPassed . '">&nbsp;</th>';
            }
        }
        $stringCalendar .= '</tr>';
        // Horas
        for($j=9 ; $j<=22 ; $j++){
            $stringCalendar .= '<tr><th class="cabeceraCalendar borderTD weekName">' . $j . ':00</th>';
            for($i=1 ; $i <=$numDaysInThisMonth ; $i++){
                $classPassed = '';
                if(($month == $todayMonth && $i < $todayDay) || ($month == $todayMonth && $i == $todayDay && $j < $todayHour)){
                    $classPassed = 'passedDay';
                }

                $class = '';
                $title = '';
                $text  = '';
                foreach($bookings as $oneBooking){
                    if($oneBooking['from'] == $i && $j >= $oneBooking['hour'] && $j < $oneBooking['toHour']){
                        $title = 'title="' . $oneBooking['bookingID'] . '"';
                        $class = 'bookedDay';
                        $text  = $oneBooking['bookingID'];
                    }
                }

                if($i <= $lastDayOfMonth) {
                    $stringCalendar .= '<td class="borderTD ' . $class . ' ' . $classPassed . '"' . $class . ' ' . $title . '>' . $text . '</td>';
                }
                else {
                    $stringCalendar .= '<td class="noAvailableDay ' . $classPassed . '">&nbsp;</td>';
                }
            }
            $stringCalendar .= '</tr>';
        }


        $stringCalendar .= '</table>';

        return $stringCalendar;
    }

    public function showCalendarByWeek($since, $to = 0, $Lang = 'es', $propertyID)
    {

        $year   = (int)substr($since, 0, 4);
        $month  = (int)substr($since, 4, 2);
        $day    = (int)substr($since, 6, 2);
        $hour   = (int)substr($since, 8, 2);

        // HORAS
        $arrayHours = array();
        if ($hour <= 15) {
            // Tramo de la mañana
            $arrayHours = array(9, 10, 11, 12, 13, 14, 15);
        } else {
            // Tramo de la tarde
            $arrayHours = array(16, 17, 18, 19, 20, 21, 22);
        }

        // DIAS
        $numberDay = date('N', strtotime($year . '-' . $month . '-' . $day));
        $numDaysBefore = $numberDay - 1;
        $numDaysAfter = 8 - $numberDay;

        $arrayDays = array();
        $arrayDays[] = 0;

        if ($numDaysBefore > 0) {
            for ($i = $numDaysBefore; $i > 0; $i--) {
                $arrayDays[] = date('Ymd', mktime(0, 0, 0, $month, $day - $i, $year));
            }
        }
        $arrayDays[] = date('Ymd', mktime(0, 0, 0, $month, $day, $year));
        if ($numDaysAfter > 0) {
            for ($i = 1; $i < $numDaysAfter; $i++) {
                $arrayDays[] = date('Ymd', mktime(0, 0, 0, $month, $day + $i, $year));
            }
        }

        // RESERVAS
        $bookings       = $this->getDoctrine()
            ->getRepository('BookingsBookingBundle:Booking')
            ->getAllBookingsInPeriod($arrayDays[1].'00', $arrayDays[7].'00', array($propertyID));

        $calendar = '<table class="table table-bordered text-center">';
        $calendar .= '<tr><th colspan="8">' . $this->nameMonths($month, $Lang) . ' ' . $year . '</th></tr>';
        foreach ($arrayDays as $day) {
            $calendar .= '<tr>';
            if($day > 0){
                $calendar .= '<td>' . substr($day, 6, 2) . ' / ' . substr($day, 4, 2) . '</td>' ;
            }
            else{
                $calendar .= '<td></td>';
            }

            foreach ($arrayHours as $hour) {
                if($day == 0) {
                    $calendar .= '<td>' . $hour . ':00</td>';
                }
                else {
                    $available = true;

                    foreach($bookings as $booking){
                        if((int)$booking['hour'] == $hour && $booking['year'].$booking['month'].$booking['from'] == $day){
                            $available = false;
                            $calendar .= '<td class="selectedDay">' . $booking['bookingID'] . '</td>';
                        }
                    }

                    if($available){
                        $calendar .= '<td></td>';
                    }
                }
            }
            $calendar .= '</tr>';
        }
        $calendar .= '</table>';

        return $calendar;
    }

    public function deleteReserveAction(){

        if (isset($_POST['reserveID'])) {
            $this->getDoctrine()
                ->getRepository('BookingsBookingBundle:Booking')
                ->cancelBooking($_POST['reserveID']);

            return new JsonResponse(array('idDelete' => $_POST['reserveID']));
        } else return new JsonResponse(array());
    }

    public function getIcs($data)
    {
        $provider = $this->get('bomo_ical.ics_provider');

        $tz = $provider->createTimezone();
        $tz->setTzid('Europe/Madrid');
        $tz->setProperty('X-LIC-LOCATION', $tz->getTzid());

        $cal = $provider->createCalendar($tz);

        //$cal->setName($data['name'] . ' iCal');
        //$cal->setDescription($data['name'] . ' iCal from www.elalmacendelocio.es');

        foreach($data['bookings'] as $booking){
            if($data['type'] == 'hour'){
                $startDate = new \Datetime($booking['year'] . '-' . $booking['month'] . '-' . $booking['from'] . ' ' . $booking['hour'] . ':00:00');
                $endDate   = new \Datetime($booking['year'] . '-' . $booking['month'] . '-' . $booking['from'] . ' ' . ((int)$booking['hour']+1) . ':00:00');
            }
            else{
                $startDate = new \Datetime($booking['year'] . '-' . $booking['month'] . '-' . $booking['from'] . ' 00:00:00');
                $endDate   = new \Datetime($booking['toYear'] . '-' . $booking['toMonth'] . '-' . $booking['toDay'] . ' 00:00:00');
            }

            $event = $cal->newEvent();
            //ldd($event);
            $event->setName($data['name'] . ' iCal from www.elalmacendelocio.es');
            $event->setStartDate($startDate);
            $event->setEndDate($endDate);
            $event->setDescription('El almacen del ocio # ' . $data['name'] . ' # ' . $booking['bookingID']);
        }

        $fs = new Filesystem();

        try {
            $fs->exists('icals');
        } catch (IOExceptionInterface $e) {
            echo "No existe el directorio " . $e->getPath();
        }

        $fileName = 'propID' . $data['id'];
        $fs->dumpFile('icals/' . $fileName . '.ics', $cal->returnCalendar());

        return 'icals/' . $fileName . '.ics';
        /*return new Response(
            $calStr,
            200,
            array(
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="calendar.ics"',
            )
        );*/
    }

    /*
     * parser ical
     * */
    public function addCalendarComponentWithKeyAndValue($component, $keyword, $value, &$last_keyword, &$todo_count, &$event_count, &$freebusy_count, &$cal)
    {
        if ($keyword == false) {
            $keyword = $last_keyword;
        }
        switch ($component) {
            case 'VTODO':
                $cal[$component][$todo_count - 1][$keyword] = $value;
                break;
            case 'VEVENT':
                if (!isset($cal[$component][$event_count - 1][$keyword . '_array'])) {
                    $cal[$component][$event_count - 1][$keyword . '_array'] = array(); // Create array()
                }
                if (is_array($value)) {
                    array_push($cal[$component][$event_count - 1][$keyword . '_array'], $value); // Add array of properties to the end
                } else {
                    if (!isset($cal[$component][$event_count - 1][$keyword])) {
                        $cal[$component][$event_count - 1][$keyword] = $value;
                    }
                    $cal[$component][$event_count - 1][$keyword . '_array'][] = $value;
                    // Glue back together for multi-line content
                    if ($cal[$component][$event_count - 1][$keyword] != $value) {
                        $ord = (isset($value[0])) ? ord($value[0]) : NULL; // First char
                        if(in_array($ord, array(9, 32))){ // Is space or tab?
                            $value = substr($value, 1); // Only trim the first character
                        }
                        if(is_array($cal[$component][$event_count - 1][$keyword . '_array'][1])){ // Account for multiple definitions of current keyword (e.g. ATTENDEE)
                            $cal[$component][$event_count - 1][$keyword] .= ';' . $value; // Concat value *with separator* as content spans multiple lines
                        } else {
                            $cal[$component][$event_count - 1][$keyword] .= $value; // Concat value as content spans multiple lines
                        }
                    }
                }
                break;
            case 'VFREEBUSY':
                $cal[$component][$freebusy_count - 1][$keyword] = $value;
                break;
            default:
                $cal[$component][$keyword] = $value;
                break;
        }
        $this->last_keyword = $keyword;
    }
    /**
     * Get a key-value pair of a string.
     *
     * @param {string} $text which is like "VCALENDAR:Begin" or "LOCATION:"
     *
     * @return {array} array("VCALENDAR", "Begin")
     */
    public function keyValueFromString($text)
    {
        // Match colon separator outside of quoted substrings
        // Fallback to nearest semicolon outside of quoted substrings, if colon cannot be found
        // Do not try and match within the value paired with the keyword
        preg_match('/(.*?)(?::(?=(?:[^"]*"[^"]*")*[^"]*$)|;(?=[^:]*$))([\w\W]*)/', $text, $matches);
        if (count($matches) == 0) {
            return false;
        }
        if (preg_match('/^([A-Z-]+)([;][\w\W]*)?$/', $matches[1])) {
            $matches = array_splice($matches, 1, 2); // Remove first match and re-align ordering
            // Process properties
            if (preg_match('/([A-Z-]+)[;]([\w\W]*)/', $matches[0], $properties)) {
                array_shift($properties); // Remove first match
                $matches[0] = $properties[0]; // Fix to ignore everything in keyword after a ; (e.g. Language, TZID, etc.)
                array_shift($properties); // Repeat removing first match
                $formatted = array();
                foreach ($properties as $property) {
                    preg_match_all('~[^\r\n";]+(?:"[^"\\\]*(?:\\\.[^"\\\]*)*"[^\r\n";]*)*~', $property, $attributes); // Match semicolon separator outside of quoted substrings
                    $attributes = (sizeof($attributes) == 0) ? array($property) : reset($attributes); // Remove multi-dimensional array and use the first key
                    foreach ($attributes as $attribute) {
                        preg_match_all('~[^\r\n"=]+(?:"[^"\\\]*(?:\\\.[^"\\\]*)*"[^\r\n"=]*)*~', $attribute, $values); // Match equals sign separator outside of quoted substrings
                        $value = (sizeof($values) == 0) ? NULL : reset($values); // Remove multi-dimensional array and use the first key
                        if (is_array($value) && isset($value[1])) {
                            $formatted[$value[0]] = trim($value[1], '"'); // Remove double quotes from beginning and end only
                        }
                    }
                }
                $properties[0] = $formatted; // Assign the keyword property information
                array_unshift($properties, $matches[1]); // Add match to beginning of array
                $matches[1] = $properties;
            }
            return $matches;
        } else {
            return false; // Ignore this match
        }
    }
    /**
     * Return Unix timestamp from iCal date time format
     *
     * @param {string} $icalDate A Date in the format YYYYMMDD[T]HHMMSS[Z] or
     *                           YYYYMMDD[T]HHMMSS
     *
     * @return {int}
     */
    public function iCalDateToUnixTimestamp($icalDate)
    {
        $icalDate = str_replace('T', '', $icalDate);
        $icalDate = str_replace('Z', '', $icalDate);
        $pattern  = '/([0-9]{4})';   // 1: YYYY
        $pattern .= '([0-9]{2})';    // 2: MM
        $pattern .= '([0-9]{2})';    // 3: DD
        $pattern .= '([0-9]{0,2})';  // 4: HH
        $pattern .= '([0-9]{0,2})';  // 5: MM
        $pattern .= '([0-9]{0,2})/'; // 6: SS
        preg_match($pattern, $icalDate, $date);
        // Unix timestamp can't represent dates before 1970
        if ($date[1] <= 1970) {
            return false;
        }
        // Unix timestamps after 03:14:07 UTC 2038-01-19 might cause an overflow
        // if 32 bit integers are used.
        $timestamp = mktime((int)$date[4], (int)$date[5], (int)$date[6], (int)$date[2], (int)$date[3], (int)$date[1]);
        return $timestamp;
    }
    /**
     * Processes recurrences
     *
     * @author John Grogg <john.grogg@gmail.com>
     * @return {array}
     */
    public function process_recurrences(&$cal)
    {
        $array = $cal;
        $events = $array['VEVENT'];
        if (empty($events))
            return false;
        foreach ($array['VEVENT'] as $anEvent) {
            if (isset($anEvent['RRULE']) && $anEvent['RRULE'] != '') {
                // Recurring event, parse RRULE and add appropriate duplicate events
                $rrules = array();
                $rrule_strings = explode(';', $anEvent['RRULE']);
                foreach ($rrule_strings as $s) {
                    list($k, $v) = explode('=', $s);
                    $rrules[$k] = $v;
                }
                // Get frequency
                $frequency = $rrules['FREQ'];
                // Get Start timestamp
                $start_timestamp = $this->iCalDateToUnixTimestamp($anEvent['DTSTART']);
                $end_timestamp = $this->iCalDateToUnixTimestamp($anEvent['DTEND']);
                $event_timestmap_offset = $end_timestamp - $start_timestamp;
                // Get Interval
                $interval = (isset($rrules['INTERVAL']) && $rrules['INTERVAL'] != '') ? $rrules['INTERVAL'] : 1;
                if (in_array($frequency, array('MONTHLY', 'YEARLY')) && isset($rrules['BYDAY']) && $rrules['BYDAY'] != '') {
                    // Deal with BYDAY
                    $day_number = intval($rrules['BYDAY']);
                    if (empty($day_number)) { // Returns 0 when no number defined in BYDAY
                        if (!isset($rrules['BYSETPOS'])) {
                            $day_number = 1; // Set first as default
                        } else if (is_numeric($rrules['BYSETPOS'])) {
                            $day_number = $rrules['BYSETPOS'];
                        }
                    }
                    $day_number = ($day_number == -1) ? 6 : $day_number; // Override for our custom key (6 => 'last')
                    $week_day = substr($rrules['BYDAY'], -2);
                    $day_ordinals = array(1 => 'first', 2 => 'second', 3 => 'third', 4 => 'fourth', 5 => 'fifth', 6 => 'last');
                    $weekdays = array('SU' => 'sunday', 'MO' => 'monday', 'TU' => 'tuesday', 'WE' => 'wednesday', 'TH' => 'thursday', 'FR' => 'friday', 'SA' => 'saturday');
                }
                $until_default = date_create('now');
                $until_default->modify($this->default_span . ' year');
                $until_default->setTime(23, 59, 59); // End of the day
                $until_default = date_format($until_default, 'Ymd\THis');
                if (isset($rrules['UNTIL'])) {
                    // Get Until
                    $until = $this->iCalDateToUnixTimestamp($rrules['UNTIL']);
                } else if (isset($rrules['COUNT'])) {
                    $frequency_conversion = array('DAILY' => 'day', 'WEEKLY' => 'week', 'MONTHLY' => 'month', 'YEARLY' => 'year');
                    $count_orig = (is_numeric($rrules['COUNT']) && $rrules['COUNT'] > 1) ? $rrules['COUNT'] : 0;
                    $count = ($count_orig - 1); // Remove one to exclude the occurrence that initialises the rule
                    $count += ($count > 0) ? $count * ($interval - 1) : 0;
                    $offset = "+$count " . $frequency_conversion[$frequency];
                    $until = strtotime($offset, $start_timestamp);
                    if (in_array($frequency, array('MONTHLY', 'YEARLY')) && isset($rrules['BYDAY']) && $rrules['BYDAY'] != '') {
                        $dtstart = date_create($anEvent['DTSTART']);
                        for ($i = 1; $i <= $count; $i++) {
                            $dtstart_clone = clone $dtstart;
                            $dtstart_clone->modify('next ' . $frequency_conversion[$frequency]);
                            $offset = "{$day_ordinals[$day_number]} {$weekdays[$week_day]} of " . $dtstart_clone->format('F Y H:i:01');
                            $dtstart->modify($offset);
                        }
                        // Jumping X months forwards doesn't mean the end date will fall on the same day defined in BYDAY
                        // Use the largest of these to ensure we are going far enough in the future to capture our final end day
                        $until = max($until, $dtstart->format('U'));
                    }
                    unset($offset);
                } else {
                    $until = $this->iCalDateToUnixTimestamp($until_default);
                }
                if(!isset($anEvent['EXDATE_array'])){
                    $anEvent['EXDATE_array'] = array();
                }
                // Decide how often to add events and do so
                switch ($frequency) {
                    case 'DAILY':
                        // Simply add a new event each interval of days until UNTIL is reached
                        $offset = "+$interval day";
                        $recurring_timestamp = strtotime($offset, $start_timestamp);
                        while ($recurring_timestamp <= $until) {
                            // Add event
                            $anEvent['DTSTART'] = date('Ymd\THis', $recurring_timestamp);
                            $anEvent['DTEND'] = date('Ymd\THis', $recurring_timestamp + $event_timestmap_offset);
                            $search_date = $anEvent['DTSTART'];
                            $is_excluded = array_filter($anEvent['EXDATE_array'], function($val) use ($search_date) { return is_string($val) && strpos($search_date, $val) === 0; });
                            if (!$is_excluded) {
                                $events[] = $anEvent;
                            }
                            // Move forwards
                            $recurring_timestamp = strtotime($offset, $recurring_timestamp);
                        }
                        break;
                    case 'WEEKLY':
                        // Create offset
                        $offset = "+$interval week";
                        // Build list of days of week to add events
                        $weekdays = array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA');
                        if (isset($rrules['BYDAY']) && $rrules['BYDAY'] != '') {
                            $bydays = explode(',', $rrules['BYDAY']);
                        } else {
                            $weekTemp = array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA');
                            $findDay = $weekTemp[date('w', $start_timestamp)];
                            $bydays = array($findDay);
                        }
                        // Get timestamp of first day of start week
                        $week_recurring_timestamp = (date('w', $start_timestamp) == 0) ? $start_timestamp : strtotime('last Sunday ' . date('H:i:s', $start_timestamp), $start_timestamp);
                        // Step through weeks
                        while ($week_recurring_timestamp <= $until) {
                            // Add events for bydays
                            $day_recurring_timestamp = $week_recurring_timestamp;
                            foreach ($weekdays as $day) {
                                // Check if day should be added
                                if (in_array($day, $bydays) && $day_recurring_timestamp > $start_timestamp && $day_recurring_timestamp <= $until) {
                                    // Add event to day
                                    $anEvent['DTSTART'] = date('Ymd\THis', $day_recurring_timestamp);
                                    $anEvent['DTEND'] = date('Ymd\THis', $day_recurring_timestamp + $event_timestmap_offset);
                                    $search_date = $anEvent['DTSTART'];
                                    $is_excluded = array_filter($anEvent['EXDATE_array'], function($val) use ($search_date) { return is_string($val) && strpos($search_date, $val) === 0; });
                                    if (!$is_excluded) {
                                        $events[] = $anEvent;
                                    }
                                }
                                // Move forwards a day
                                $day_recurring_timestamp = strtotime('+1 day', $day_recurring_timestamp);
                            }
                            // Move forwards $interval weeks
                            $week_recurring_timestamp = strtotime($offset, $week_recurring_timestamp);
                        }
                        break;
                    case 'MONTHLY':
                        // Create offset
                        $offset = "+$interval month";
                        $recurring_timestamp = strtotime($offset, $start_timestamp);
                        if (isset($rrules['BYMONTHDAY']) && $rrules['BYMONTHDAY'] != '') {
                            // Deal with BYMONTHDAY
                            $monthdays = explode(',', $rrules['BYMONTHDAY']);
                            while ($recurring_timestamp <= $until) {
                                foreach ($monthdays as $monthday) {
                                    // Add event
                                    $anEvent['DTSTART'] = date('Ym' . sprintf('%02d', $monthday) . '\THis', $recurring_timestamp);
                                    $anEvent['DTEND'] = date('Ymd\THis', $this->iCalDateToUnixTimestamp($anEvent['DTSTART']) + $event_timestmap_offset);
                                    $search_date = $anEvent['DTSTART'];
                                    $is_excluded = array_filter($anEvent['EXDATE_array'], function($val) use ($search_date) { return is_string($val) && strpos($search_date, $val) === 0; });
                                    if (!$is_excluded) {
                                        $events[] = $anEvent;
                                    }
                                }
                                // Move forwards
                                $recurring_timestamp = strtotime($offset, $recurring_timestamp);
                            }
                        } else if (isset($rrules['BYDAY']) && $rrules['BYDAY'] != '') {
                            $start_time = date('His', $start_timestamp);
                            while ($recurring_timestamp <= $until) {
                                $event_start_desc = "{$day_ordinals[$day_number]} {$weekdays[$week_day]} of " . date('F Y H:i:s', $recurring_timestamp);
                                $event_start_timestamp = strtotime($event_start_desc);
                                if ($event_start_timestamp > $start_timestamp && $event_start_timestamp < $until) {
                                    $anEvent['DTSTART'] = date('Ymd\T', $event_start_timestamp) . $start_time;
                                    $anEvent['DTEND'] = date('Ymd\THis', $this->iCalDateToUnixTimestamp($anEvent['DTSTART']) + $event_timestmap_offset);
                                    $search_date = $anEvent['DTSTART'];
                                    $is_excluded = array_filter($anEvent['EXDATE_array'], function($val) use ($search_date) { return is_string($val) && strpos($search_date, $val) === 0; });
                                    if (!$is_excluded) {
                                        $events[] = $anEvent;
                                    }
                                }
                                // Move forwards
                                $recurring_timestamp = strtotime($offset, $recurring_timestamp);
                            }
                        }
                        break;
                    case 'YEARLY':
                        // Create offset
                        $offset = "+$interval year";
                        $recurring_timestamp = strtotime($offset, $start_timestamp);
                        $month_names = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December');
                        // Check if BYDAY rule exists
                        if (isset($rrules['BYDAY']) && $rrules['BYDAY'] != '') {
                            $start_time = date('His', $start_timestamp);
                            while ($recurring_timestamp <= $until) {
                                $event_start_desc = "{$day_ordinals[$day_number]} {$weekdays[$week_day]} of {$month_names[$rrules['BYMONTH']]} " . date('Y H:i:s', $recurring_timestamp);
                                $event_start_timestamp = strtotime($event_start_desc);
                                if ($event_start_timestamp > $start_timestamp && $event_start_timestamp < $until) {
                                    $anEvent['DTSTART'] = date('Ymd\T', $event_start_timestamp) . $start_time;
                                    $anEvent['DTEND'] = date('Ymd\THis', $this->iCalDateToUnixTimestamp($anEvent['DTSTART']) + $event_timestmap_offset);
                                    $search_date = $anEvent['DTSTART'];
                                    $is_excluded = array_filter($anEvent['EXDATE_array'], function($val) use ($search_date) { return is_string($val) && strpos($search_date, $val) === 0; });
                                    if (!$is_excluded) {
                                        $events[] = $anEvent;
                                    }
                                }
                                // Move forwards
                                $recurring_timestamp = strtotime($offset, $recurring_timestamp);
                            }
                        } else {
                            $day = date('d', $start_timestamp);
                            $start_time = date('His', $start_timestamp);
                            // Step through years
                            while ($recurring_timestamp <= $until) {
                                // Add specific month dates
                                if (isset($rrules['BYMONTH']) && $rrules['BYMONTH'] != '') {
                                    $event_start_desc = "$day {$month_names[$rrules['BYMONTH']]} " . date('Y H:i:s', $recurring_timestamp);
                                } else {
                                    $event_start_desc = $day . date('F Y H:i:s', $recurring_timestamp);
                                }
                                $event_start_timestamp = strtotime($event_start_desc);
                                if ($event_start_timestamp > $start_timestamp && $event_start_timestamp < $until) {
                                    $anEvent['DTSTART'] = date('Ymd\T', $event_start_timestamp) . $start_time;
                                    $anEvent['DTEND'] = date('Ymd\THis', $this->iCalDateToUnixTimestamp($anEvent['DTSTART']) + $event_timestmap_offset);
                                    $search_date = $anEvent['DTSTART'];
                                    $is_excluded = array_filter($anEvent['EXDATE_array'], function($val) use ($search_date) { return is_string($val) && strpos($search_date, $val) === 0; });
                                    if (!$is_excluded) {
                                        $events[] = $anEvent;
                                    }
                                }
                                // Move forwards
                                $recurring_timestamp = strtotime($offset, $recurring_timestamp);
                            }
                        }
                        break;
                        $events = (isset($count_orig) && sizeof($events) > $count_orig) ? array_slice($events, 0, $count_orig) : $events; // Ensure we abide by COUNT if defined
                }
            }
        }
        $this->cal['VEVENT'] = $events;
    }
    /**
     * Returns an array of arrays with all events. Every event is an associative
     * array and each property is an element it.
     *
     * @return {array}
     */
    public function events()
    {
        $array = $this->cal;
        return $array['VEVENT'];
    }
    /**
     * Returns the calendar name
     *
     * @return {calendar name}
     */
    public function calendarName()
    {
        return $this->cal['VCALENDAR']['X-WR-CALNAME'];
    }
    /**
     * Returns the calendar description
     *
     * @return {calendar description}
     */
    public function calendarDescription()
    {
        return $this->cal['VCALENDAR']['X-WR-CALDESC'];
    }
    /**
     * Returns an array of arrays with all free/busy events. Every event is
     * an associative array and each property is an element it.
     *
     * @return {array}
     */
    public function freeBusyEvents()
    {
        $array = $this->cal;
        return $array['VFREEBUSY'];
    }
    /**
     * Returns a boolean value whether thr current calendar has events or not
     *
     * @return {boolean}
     */
    public function hasEvents()
    {
        return (count($this->events()) > 0) ? true : false;
    }
    /**
     * Returns false when the current calendar has no events in range, else the
     * events.
     *
     * Note that this function makes use of a UNIX timestamp. This might be a
     * problem on January the 29th, 2038.
     * See http://en.wikipedia.org/wiki/Unix_time#Representing_the_number
     *
     * @param {boolean} $rangeStart Either true or false
     * @param {boolean} $rangeEnd   Either true or false
     *
     * @return {mixed}
     */
    public function eventsFromRange($rangeStart = false, $rangeEnd = false)
    {
        $events = $this->sortEventsWithOrder($this->events(), SORT_ASC);
        if (!$events) {
            return false;
        }
        $extendedEvents = array();
        if ($rangeStart === false) {
            $rangeStart = new DateTime();
        } else {
            $rangeStart = new DateTime($rangeStart);
        }
        if ($rangeEnd === false or $rangeEnd <= 0) {
            $rangeEnd = new DateTime('2038/01/18');
        } else {
            $rangeEnd = new DateTime($rangeEnd);
        }
        $rangeStart = $rangeStart->format('U');
        $rangeEnd   = $rangeEnd->format('U');
        // Loop through all events by adding two new elements
        foreach ($events as $anEvent) {
            $timestamp = $this->iCalDateToUnixTimestamp($anEvent['DTSTART']);
            if ($timestamp >= $rangeStart && $timestamp <= $rangeEnd) {
                $extendedEvents[] = $anEvent;
            }
        }
        return $extendedEvents;
    }
    /**
     * Returns a boolean value whether the current calendar has events or not
     *
     * @param {array} $events    An array with events.
     * @param {array} $sortOrder Either SORT_ASC, SORT_DESC, SORT_REGULAR,
     *                           SORT_NUMERIC, SORT_STRING
     *
     * @return {boolean}
     */
    public function sortEventsWithOrder($events, $sortOrder = SORT_ASC)
    {
        $extendedEvents = array();
        // Loop through all events by adding two new elements
        foreach ($events as $anEvent) {
            if (!array_key_exists('UNIX_TIMESTAMP', $anEvent)) {
                $anEvent['UNIX_TIMESTAMP'] = $this->iCalDateToUnixTimestamp($anEvent['DTSTART']);
            }
            if (!array_key_exists('REAL_DATETIME', $anEvent)) {
                $anEvent['REAL_DATETIME'] = date('d.m.Y', $anEvent['UNIX_TIMESTAMP']);
            }
            $extendedEvents[] = $anEvent;
        }
        foreach ($extendedEvents as $key => $value) {
            $timestamp[$key] = $value['UNIX_TIMESTAMP'];
        }
        array_multisort($timestamp, $sortOrder, $extendedEvents);
        return $extendedEvents;
    }
}