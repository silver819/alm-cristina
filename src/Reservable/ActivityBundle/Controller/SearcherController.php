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

        // Borramos datos que tengamos anteriormente en la sesion
        $session->remove('searchProvince');
        $session->remove('searchCity');
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
		$relevantFields = array("province", "city", "name", "type", "hour");

		foreach($_POST as $field => $value){
			if($value != '' && in_array($field, $relevantFields)){
				switch ($field){
					case "city":
						$where .= " AND p.zone = " . $value;
						$session->set('searchCity', $value);

						break;
                    case "province":

                        $session->set('searchProvince', $value);

                        break;

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

		$results    = array();

		$images      = array();
        $resultsAux2 = array();
        $arrayPrices = array();
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
            else{
                $resultsAux2 = $resultsAux;
            }

			foreach($resultsAux2 as $oneResult){
				// Buscamos disponibilidad
				$bookings = $this->getDoctrine()
								 ->getRepository('BookingsBookingBundle:Booking')
								 ->findBookingsFromPropertyID($oneResult->getId());

                if(!isset($thisRange)){
                    $thisRange[] = date('Ymd', strtotime('+2 days')) . '09';
                }

				$dispo = $this->getDoctrine()
							  ->getRepository('BookingsBookingBundle:DisponibilityBooking')
							  ->findDispoInThisRange($bookings, $thisRange);

				// Tenemos disponibilidad
				if(empty($dispo)){

                    // Calculamos precio
                    $thisPrices = $this->getPriceActivityByRange($oneResult->getId(), $thisRange);
                    if(!empty($thisPrices)){
                        $arrayPrices[$oneResult->getId()] = $thisPrices;
                    }

					$results[] = $oneResult;

                    $pictures = $this->getDoctrine()
                        ->getRepository('ReservableActivityBundle:Picture')
                        ->findAllByPropertyID($oneResult->getId());

                    foreach($pictures as $onePicture){
                        $images[$oneResult->getId()][] = $onePicture['path'];
                    }
				}
			}
		}

        // Resultados definitivos con precios
        $resultsFromatted = array();
        $arrayComments = array();
        $arrayRatings = array();
        foreach($results as $result){
            if(is_object($result)) {
                $key = $result->getId();
                if (array_key_exists($key, $arrayPrices)) {
                    $resultsFromatted[] = $result;
                }

                // Comentarios y valoraciones
                $comments       = $this->getComments($result->getId());
                $resultRatings  = $this->getRatings($result->getId());
                $arrayComments[$result->getId()] = count($comments);
                $arrayRatings[$result->getId()]  = ($resultRatings['totalScore'] * 2) . " / 10";
            }
        }

        //ldd($session->get('filterSearch'));
        //ld($images);

        $arrayCitiesQuery = $this->getDoctrine()->getRepository('ReservableActivityBundle:Zone')->findBy(array('type' => 4), array('name' => 'ASC'));
        $cities = array();
        foreach($arrayCitiesQuery as $city){
            $childrens = $this->getDoctrine()->getRepository('ReservableActivityBundle:Zone')->findBy(array('type' => 5, 'fatherZone' => $city->getId()), array('name' => 'ASC'));
            $arrayChildrens = array();
            if($childrens) {
                foreach ($childrens as $children) {
                    $arrayChildrens[] = array('id' => $children->getId(), 'name' => $children->getName());
                }
            }
            $cities[] = array('id' => $city->getId(), 'name' => $city->getName(), 'childrens' => $arrayChildrens);
        }

		return $this->render('ReservableActivityBundle:Search:displayResults.html.twig', 
			array(
                  "cities" => $cities,
                  "filters" => $filters,
                  "results" => $resultsFromatted,
                  'arrayPrices' => $arrayPrices,
                  'images' => $images,
                  'arrayComments' => $arrayComments,
                  'arrayRatings' => $arrayRatings
            )
        );
	}

    public function getComments($propertyID){

    $resultQuery = $this->getDoctrine()
    ->getManager()
    ->createQuery("SELECT r.comentarios
                               FROM RagingsRatingBundle:Rating r INNER JOIN BookingsBookingBundle:Booking b
                               WHERE r.reservationNumber = b.id
                               AND b.activityID = '" . $propertyID . "'
                               AND r.comentarios != ''")
    ->getResult();

    return $resultQuery;
    }

    public function getRatings($propertyID)
    {

        $resultQuery = $this->getDoctrine()
            ->getManager()
            ->createQuery("SELECT r.ubicacion, r.llegar, r.limpieza, r.material, r.caracteristicas, r.gestiones, r.usabilidad
                               FROM RagingsRatingBundle:Rating r INNER JOIN BookingsBookingBundle:Booking b
                               WHERE r.reservationNumber = b.id
                               AND b.activityID = '" . $propertyID . "'
                               AND r.comentarios != ''")
            ->getResult();

        $mean = array();
        $mean['ubicacion'] = 0;
        $mean['llegar'] = 0;
        $mean['limpieza'] = 0;
        $mean['material'] = 0;
        $mean['caracteristicas'] = 0;
        $mean['gestiones'] = 0;
        $mean['usabilidad'] = 0;
        $total = 0;
        $cont = 0;
        if (!empty($resultQuery)) {
            foreach ($resultQuery as $oneResult) {
                $mean['ubicacion'] += $oneResult['ubicacion'];
                $total += $oneResult['ubicacion'];
                $mean['llegar'] += $oneResult['llegar'];
                $total += $oneResult['llegar'];
                $mean['limpieza'] += $oneResult['limpieza'];
                $total += $oneResult['limpieza'];
                $mean['material'] += $oneResult['material'];
                $total += $oneResult['material'];
                $mean['caracteristicas'] += $oneResult['caracteristicas'];
                $total += $oneResult['caracteristicas'];
                $mean['gestiones'] += $oneResult['gestiones'];
                $total += $oneResult['gestiones'];
                $mean['usabilidad'] += $oneResult['usabilidad'];
                $total += $oneResult['usabilidad'];

                $cont++;
            }

            $mean['ubicacion'] = round($mean['ubicacion'] / $cont, 2);
            $mean['llegar'] = round($mean['llegar'] / $cont, 2);
            $mean['limpieza'] = round($mean['limpieza'] / $cont, 2);
            $mean['material'] = round($mean['material'] / $cont, 2);
            $mean['caracteristicas'] = round($mean['caracteristicas'] / $cont, 2);
            $mean['gestiones'] = round($mean['gestiones'] / $cont, 2);
            $mean['usabilidad'] = round($mean['usabilidad'] / $cont, 2);

            $total = $total / ($cont * count($mean));
        }

        return array('ratings' => $mean, 'totalScore' => round($total, 2));
    }

    private function getPriceActivityByRange($propertyID, $arrayDates){

        $prices = array();
        $activity = $this->getDoctrine()->getRepository('ReservableActivityBundle:Activity')->findOneBy(array('id' => $propertyID));

        if($activity->getTypeRent() == 'day') {
            $seasons = $this->getDoctrine()
                ->getManager()
                ->createQuery("SELECT s.startSeason, s.endSeason, s.price
                           FROM ReservableActivityBundle:Seasons s
                           WHERE s.activityID = " . $propertyID . " AND s.endSeason >= '" . date('Ymd') . "'")
                ->getResult();

            if (!empty($seasons)) {
                foreach ($arrayDates as $day) {

                    $currentDay = (int)substr($day, 0, 8);

                    foreach($seasons as $season){
                        if((int)$season['startSeason'] <= $currentDay && $currentDay < (int)$season['endSeason']){
                            $prices[$currentDay] = $season['price'];
                        }
                    }
                }

                $prices['totalPrice'] = array_sum($prices);
                $prices['priceByDay'] = round($prices['totalPrice'] / count($arrayDates), 2);
            }
        }
        else{
            $seasons = $this->getDoctrine()
                ->getManager()
                ->createQuery("SELECT s.startSeason, s.endSeason, s.price
                           FROM ReservableActivityBundle:Seasons s
                           WHERE s.activityID = " . $propertyID)
                ->getResult();

            if (!empty($seasons)) {
                foreach ($arrayDates as $day) {

                    $currentHour = (int)substr($day, 8, 2);

                    foreach($seasons as $season){
                        if((int)$season['startSeason'] <= $currentHour && $currentHour < (int)$season['endSeason']){
                            $prices[$currentHour . ':00'] = $season['price'];
                        }
                    }
                }

                $prices['totalPrice'] = array_sum($prices);
                $prices['priceByDay'] = round($prices['totalPrice'] / count($arrayDates), 2);
            }
        }

        return $prices;
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
