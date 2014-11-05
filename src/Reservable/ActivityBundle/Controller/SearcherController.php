<?php

namespace Reservable\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Activity;
use Bookings\BookingBundle\Entity\Booking;
use Bookings\BookingBundle\Entity\DisponibilityBooking;

class SearcherController extends Controller
{
	public function searchAction(){

		$where = "1=1";
		$relevantFields = array("name", "type");

		foreach($_POST as $field => $value){
			if($value != '' && in_array($field, $relevantFields)){
				switch ($field){
					case "name":
						$where .= " AND p.name LIKE '%" . $value . "%'";
						$_SESSION['search']['name'] = $value;

						break;

					case "type":
						$where .= " AND p.typeRent LIKE '" . $value . "'";
						$_SESSION['search']['type'] = $value;

						$thisRange = array();
						if($value == 'hour'){
							list($day, $month, $year) = explode('/', $_POST['date']);
							$thisRange[] = $year.$month.$day.$_POST['hour'];

							$_SESSION['search']['date']					= $_POST['date'];
							$_SESSION['search']['dateDay']				= (int)$day;
							$_SESSION['search']['dateMonth']			= (int)$month - 1;
							$_SESSION['search']['dateYear']				= (int)$year;
							$_SESSION['search']['StartDateComplete']	= $year.$month.$day.$_POST['hour'];
							$_SESSION['search']['EndDateComplete'] 		= '';

						}
						else{
							list($SDday, $SDmonth, $SDyear) = explode('/', $_POST['StartDate']);
							list($EDday, $EDmonth, $EDyear) = explode('/', $_POST['EndDate']);

							$thisDate = $SDyear.$SDmonth.$SDday.'00';
							$lastDate = $EDyear.$EDmonth.$EDday.'00';
							$_SESSION['search']['StartDateComplete']	= $thisDate;
							$_SESSION['search']['EndDateComplete'] 		= $lastDate;
							$_SESSION['search']['StartDate'] 			= $_POST['StartDate'];
							$_SESSION['search']['StartDateDay']			= (int)$SDday;
							$_SESSION['search']['StartDateMonth']		= (int)$SDmonth;
							$_SESSION['search']['StartDateYear']		= (int)$SDyear;
							$_SESSION['search']['EndDate'] 				= $_POST['EndDate'];
							$_SESSION['search']['EndDateDay']			= (int)$EDday;
							$_SESSION['search']['EndDateMonth']			= (int)$EDmonth;
							$_SESSION['search']['EndDateYear']			= (int)$EDyear;
							while($thisDate != $lastDate){
								$thisRange[] = $thisDate;
								$thisYear	 = substr($thisDate, 0, 4);
								$thisMonth	 = substr($thisDate, 4, 2);
								$thisDay 	 = substr($thisDate, 6, 2);
								$thisDate 	 = date('Ymd', mktime(0,0,0,$thisMonth,$thisDay+1,$thisYear)).'00';
							}
						}

						break;
				}
			}
		}

		$resultsAux = $this->getDoctrine()
						->getRepository('ReservableActivityBundle:Activity')
						->getPropertiesWhere($where);
		$results = array();

		$images = array();
		if(!empty($resultsAux)){
			foreach($resultsAux as $oneResult){
				// Buscamos disponibilidad
				$bookings = $this->getDoctrine()
								 ->getRepository('BookingsBookingBundle:Booking')
								 ->findBookingsFromPropertyID($oneResult->getId());

				$dispo = $this->getDoctrine()
							  ->getRepository('BookingsBookingBundle:DisponibilityBooking')
							  ->findDispoInThisRange($bookings, $thisRange);

				// Tenemos disponibilidad
				if(empty($dispo)){
					$results[] = $oneResult;

					$firstImage = $this->getDoctrine()
									   ->getRepository('ReservableActivityBundle:Picture')
									   ->findAllByPropertyID($oneResult->getId());

					if(!empty($firstImage[0]['path'])){
						$images[$oneResult->getId()] = $firstImage[0]['path'];
					}
				}
			}
		}

		return $this->render('ReservableActivityBundle:Search:displayResults.html.twig', 
			array("selection" => $_SESSION['search'], "results" => $results, 'images' => $images));
	}
}
