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
