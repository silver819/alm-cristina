<?php

namespace Reservable\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Activity;
use Bookings\BookingBundle\Entity\Booking;
use Bookings\BookingBundle\Entity\DisponibilityBooking;
use Symfony\Component\HttpFoundation\Request;

class SearcherController extends Controller
{
	public function searchAction(Request $request){

		$session = $request->getSession();

        //ladybug_dump($_POST);die();

        // Borramos datos que tengamos anteriormente en la sesion
        $session->remove('searchName');
        $session->remove('searchHour');
        $session->remove('searchType');
        $session->remove('searchName');
        $session->remove('searchDate');
        $session->remove('searchdateDay');
        $session->remove('searchdateMonth');
        $session->remove('searchdateYear');
        $session->remove('searchStartDateComplete');
        $session->remove('searchEndDateComplete');
        $session->remove('searchDays');
        $session->remove('searchType');
        $session->remove('searchStartDateComplete');
        $session->remove('searchEndDateComplete');
        $session->remove('searchStartDate');
        $session->remove('searchStartDateDay');
        $session->remove('searchStartDateMonth');
        $session->remove('searchStartDateYear');
        $session->remove('searchEndDate');
        $session->remove('searchEndDateDay');
        $session->remove('searchEndDateMonth');
        $session->remove('searchEndDateYear');
        $session->remove('searchDays');
        $session->remove('searchTotalDays');
        $session->remove('filterSearch');

        $filters = $this->getFilters();

		$where = "1=1";
		$relevantFields = array("name", "type", "hour");

		foreach($_POST as $field => $value){
			if($value != '' && in_array($field, $relevantFields)){
				switch ($field){
					case "name":
						$where .= " AND p.name LIKE '%" . $value . "%'";
						$session->set('searchName', $value);

						break;

					case "hour":
						$session->set('searchHour', $value);

						break;

					case "type":
						$where .= " AND p.typeRent LIKE '" . $value . "'";

						$session->set('searchType', $value);

						$thisRange = array();
						if($value == 'hour'){
							list($day, $month, $year) = explode('/', $_POST['date']);
							$thisRange[] = $year.$month.$day.$_POST['hour'];

							$session->set('searchType', 				'hour');
							$session->set('searchDate', 				$_POST['date']);
							$session->set('searchdateDay', 				(int)$day);
							$session->set('searchdateMonth', 			(int)$month - 1);
							$session->set('searchdateYear', 			(int)$year);
							$session->set('searchStartDateComplete',	$year.$month.$day.$_POST['hour']);
							$session->set('searchEndDateComplete', 		'');
							$session->set('searchDays', 				$thisRange);

						}
						else{
							list($SDday, $SDmonth, $SDyear) = explode('/', $_POST['StartDate']);
							list($EDday, $EDmonth, $EDyear) = explode('/', $_POST['EndDate']);

							$thisDate = $SDyear.$SDmonth.$SDday.'00';
							$lastDate = $EDyear.$EDmonth.$EDday.'00';
							$session->set('searchType', 				'day');
							$session->set('searchStartDateComplete',	$thisDate);
							$session->set('searchEndDateComplete', 		$lastDate);
							$session->set('searchStartDate', 			$_POST['StartDate']);
							$session->set('searchStartDateDay', 		(int)$SDday);
							$session->set('searchStartDateMonth', 		(int)$SDmonth);
							$session->set('searchStartDateYear', 		(int)$SDyear);
							$session->set('searchEndDate', 				$_POST['EndDate']);
							$session->set('searchEndDateDay', 			(int)$EDday);
							$session->set('searchEndDateMonth', 		(int)$EDmonth);
							$session->set('searchEndDateYear', 			(int)$EDyear);

							while($thisDate != $lastDate){
								$thisRange[] = $thisDate;
								$thisYear	 = substr($thisDate, 0, 4);
								$thisMonth	 = substr($thisDate, 4, 2);
								$thisDay 	 = substr($thisDate, 6, 2);
								$thisDate 	 = date('Ymd', mktime(0,0,0,$thisMonth,$thisDay+1,$thisYear)).'00';
							}

							// Días completos
							$session->set('searchDays', $thisRange);
							$session->set('searchTotalDays', count($thisRange));
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

            // Aplicamos los filtros
            if(!empty($_POST['filterSearch'])){

                $session->set('filterSearch', $_POST['filterSearch']);

                foreach($resultsAux as $result){
                    $properties[] = $result->getId();
                }

                $results = $this->getDoctrine()
                    ->getManager()
                    ->createQuery('SELECT t.activityID FROM ReservableActivityBundle:ActivityyToType t WHERE t.activityID IN (' . implode(',', $properties) . ') AND t.typeID IN (' . implode(',', $_POST['filterSearch']) . ')')
                    ->getResult();


                if(!empty($results)) {
                    $validProperties = array();
                    foreach ($results as $result) {
                        $validProperties[] = $result['activityID'];
                    }
                    
                    foreach ($resultsAux as $result) {
                        if (in_array($result->getId(), $validProperties)) {
                            $resultsAux2[] = $result;
                        }
                    }
                }
            }
            else $resultsAux2 = $resultsAux;

			foreach($resultsAux2 as $oneResult){
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

                    $pictures = $this->getDoctrine()
                        ->getRepository('ReservableActivityBundle:Picture')
                        ->findAllByPropertyID($oneResult->getId());

                    foreach($pictures as $onePicture){
                        $images[$oneResult->getId()][] = $onePicture['path'];
                    }




					/*$firstImage = $this->getDoctrine()
									   ->getRepository('ReservableActivityBundle:Picture')
									   ->findAllByPropertyID($oneResult->getId());

					if(!empty($firstImage[0]['path'])){
						$images[$oneResult->getId()] = $firstImage[0]['path'];
					}*/
				}
			}
		}

        //ld($images);

		return $this->render('ReservableActivityBundle:Search:displayResults.html.twig', 
			array("filters" => $filters, "results" => $results, 'images' => $images));
	}

    public function getFilters(){
        $arrayReturn = array();

        $results = $this->getDoctrine()
            ->getManager()
            ->createQuery('SELECT t.id, t.name, t.mode FROM ReservableActivityBundle:TypeActivity t')
            ->getResult();

        foreach($results as $result){
            $arrayReturn[$result['mode']][] = array('id' => $result['id'], 'name' => $result['name']);
        }

        return $arrayReturn;
    }
}
