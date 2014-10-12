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
						$where .= " AND p.name LIKE '%" . $value . "'%";
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

		return $this->render('ReservableActivityBundle:Search:displayResults.html.twig', array("results" => $results));
	}
}
