<?php

namespace Bookings\BookingBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
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

                $aux['calendar']        = $this->showCalendar($aux['startDate'], $aux['endDate'], $request->getLocale(), $propertyData->getId());

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

            $aux['calendar']        = $this->showCalendar($aux['startDate'], $aux['endDate'], $request->getLocale(), $propertyData->getId());

            $results[] = $aux;
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

        if($selector[0]['type'] == 'hour'){
            $calendar .= '<div class="row clearfix"><div class="col-md-12 column">';
            $calendar .= $this->showCalendarByDay($todayYear . $todayMonth . "0100", false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div><div class="col-md-12 column"><br/>';
            $calendar .= $this->showCalendarByDay(date("Ymd", mktime(0, 0, 0, $todayMonth+1, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div><div class="col-md-12 column"><br/>';
            $calendar .= $this->showCalendarByDay(date("Ymd", mktime(0, 0, 0, $todayMonth+2, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div></div>';
        }
        else{
            $calendar .= '<div class="row clearfix"><div class="col-md-4 column">';
            $calendar .= $this->showCalendar($todayYear . $todayMonth . "0100", false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+1, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+2, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+3, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+4, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+5, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+6, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+7, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+8, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div></div><div class="row clearfix"><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+9, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+10, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div><div class="col-md-4 column">';
            $calendar .= $this->showCalendar(date("Ymd", mktime(0, 0, 0, $todayMonth+11, 1, $todayYear)), false, $request->getLocale(), $selector[0]['id']);
            $calendar .= '</div></div>';
        }

        return $this->render('BookingsBookingBundle:Consult:calendar-bookings.html.twig',
            array('calendar'     => $calendar, 'selector'  => $selector));
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

        return new JsonResponse(array('calendar'=> $calendar, 'nameLodging' => $nameLodging));
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
                    if($oneBooking['from'] <= $currentDay && $currentDay < $oneBooking['to'] && !in_array($currentDay, $daysPrinted)){
                        $printDay = false;
                        $daysPrinted[] = $currentDay;
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

    private function showCalendarByDay($since, $to = 0, $Lang = 'es', $propertyID){

        $stringCalendar = "";
        $SDday          = substr($since, 6, 2);
        $SDmonth        = substr($since, 4, 2);
        $SDyear         = substr($since, 0, 4);

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

        $stringCalendar .= '<table class="titleCalendar calendarByDay"><tr><td colspan="32">' . $this->nameMonths($month, $Lang) . " " . $year . '</td></tr><tr><td></td>';
        for($i=1 ; $i <= 31 ; $i++) {
            if($i <= $lastDayOfMonth) {
                $stringCalendar .= '<td class="cabeceraCalendar borderTD">' . $i . '</td>';
            }
            else {
                $stringCalendar .= '<td class="noAvailableDay">&nbsp;</td>';
            }
        }
        $stringCalendar .= '</tr>';

        // Horas
        for($j=9 ; $j<=22 ; $j++){
            $stringCalendar .= '<tr><td class="cabeceraCalendar borderTD">' . $j . ':00</td>';
            for($i=1 ; $i <=31 ; $i++){
                $class = '';
                $title = '';
                $text  = '';
                foreach($bookings as $oneBooking){
                    if($oneBooking['from'] == $i && $j == $oneBooking['hour']){
                        $title = 'title="' . $oneBooking['bookingID'] . '"';
                        $class = 'bookedDay';
                        $text  = $oneBooking['bookingID'];
                    }
                }

                if($i <= $lastDayOfMonth) {
                    $stringCalendar .= '<td class="borderTD ' . $class . '"' . $class . ' ' . $title . '>' . $text . '</td>';
                }
                else {
                    $stringCalendar .= '<td class="noAvailableDay">&nbsp;</td>';
                }
            }
            $stringCalendar .= '</tr>';
        }


        $stringCalendar .= '</table>';

        return $stringCalendar;
    }
}