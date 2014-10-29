<?php

namespace Reservable\ActivityBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Activity;

class ViewController extends Controller
{
    public function viewAction()
	{
		if (!$this->get('security.context')->isGranted('ROLE_USER')) {
	        throw new AccessDeniedException();
	    }

		$ownerID = $this->get('security.context')->getToken()->getUser()->getId();	

		$properties = $this->getDoctrine()
						   ->getRepository('ReservableActivityBundle:Activity')
						   ->findAllByOwnerID($ownerID);

		$arrayPictures = array();
		if(!empty($properties)){
			foreach($properties as $oneResult){
				$firstImage = $this->getDoctrine()
								   ->getRepository('ReservableActivityBundle:Picture')
								   ->findAllByPropertyID($oneResult->getId());

				if(!empty($firstImage[0]['path'])){
					$arrayPictures[$oneResult->getId()] = $firstImage[0]['path'];
				}
			}
		}


		return $this->render('ReservableActivityBundle:View:viewActivities.html.twig', 
			array('properties' => $properties, 'pictures' => $arrayPictures));

    }

    public function activePropertyAction(){
		if($_POST['productID']){
			if($this->getDoctrine()
					->getRepository('ReservableActivityBundle:Activity')
					->activeProperty($_POST['productID'])){
				
				return $this->redirect('view-properties');
			}
			else{
				die("No se ha podido activar la propiedad " . $_POST['productID']);
			}
		}
		else{
			die("No se ha encontrado la propiedad " . $_POST['productID']);
		}
	}

	public function deactivePropertyAction(){
		if($_POST['productID']){
			if($this->getDoctrine()
					->getRepository('ReservableActivityBundle:Activity')
					->deactiveProperty($_POST['productID'])){
				
				return $this->redirect('view-properties');
			}
			else{
				die("No se ha podido desactivar la propiedad " . $_POST['productID']);
			}
		}
		else{
			die("No se ha encontrado la propiedad " . $_POST['productID']);
		}
	}

    public function modifyPropertyAction(){
    	$excludeFields 	= array('productID');
    	$setValues 		= '';
    	foreach($_POST as $oneField => $fieldValue){
    		if(!in_array($oneField, $excludeFields)){
    			$setValues .= "p." . $oneField . " = '" . $fieldValue . "', ";
    		}

    	}

		if($setValues != ''){
			$setValues = substr($setValues, 0, -2);
	
			$this->getDoctrine()
				 ->getRepository('ReservableActivityBundle:Activity')
				 ->setValues($_POST['productID'], $setValues);
		}

		return $this->redirect('view-properties');
	}

    public function deletePropertyAction(){
    	if($_POST['productID']){
			if($this->getDoctrine()
					->getRepository('ReservableActivityBundle:Activity')
					->deleteProperty($_POST['productID'])){

				$this->getDoctrine()
					->getRepository('ReservableActivityBundle:Picture')
					->deleteAllByPropertyID($_POST['productID']);
				
				return $this->redirect('view-properties');
			}
			else{
				die("No se ha podido eliminar la propiedad " . $_POST['productID']);
			}
		}
		else{
			die("No se ha encontrado la propiedad " . $_POST['productID']);
		}
	}

	public function viewDetailsAction($property){

		$details = $this->getDoctrine()
						->getRepository('ReservableActivityBundle:Activity')
						->findByPropertyID($property);

		$details = $details[0];

		$pictures = $this->getDoctrine()
						 ->getRepository('ReservableActivityBundle:Picture')
					     ->findAllByPropertyID($property);

		$arrayPictures = array();
		foreach($pictures as $onePicture){
			$arrayPictures[] = $onePicture['path'];
		}

		return $this->render('ReservableActivityBundle:View:detailsProperty.html.twig', 
			array('details' => $details, 'pictures' => $arrayPictures));
	}
}