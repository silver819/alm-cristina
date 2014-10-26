<?php

namespace Reservable\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Activity;

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
						break;

					case "type":
						$where .= " AND p.typeRent LIKE '" . $value . "'";

						$thisRange = array();
						if($value == 'hour'){
							list($day, $month, $year) = explode('/', $_POST['date']);
							$thisRange[] = $year.$month.$day.$_POST['hour'];
						}
						else{
							list($SDday, $SDmonth, $SDyear) = explode('/', $_POST['StartDate']);
							list($EDday, $EDmonth, $EDyear) = explode('/', $_POST['EndDate']);

							$thisDate = $SDyear.$SDmonth.$SDday.'00';
							$lastDate = $EDyear.$EDmonth.$EDday.'00';
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

		$results = $this->getDoctrine()
						->getRepository('ReservableActivityBundle:Activity')
						->getPropertiesWhere($where);

		$images = array();
		if(!empty($results)){
			foreach($results as $oneResult){
				// Buscamos dispo

				$firstImage = $this->getDoctrine()
								   ->getRepository('ReservableActivityBundle:Picture')
								   ->findAllByPropertyID($oneResult->getId());

				if(!empty($firstImage[0]['path'])){
					$images[$oneResult->getId()] = $firstImage[0]['path'];
				}
			}
		}

		return $this->render('ReservableActivityBundle:Search:displayResults.html.twig', 
			array("selection" => $_POST, "results" => $results, 'images' => $images));
	}
}
