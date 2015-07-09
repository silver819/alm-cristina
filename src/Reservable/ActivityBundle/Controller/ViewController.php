<?php

namespace Reservable\ActivityBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Activity;
use Symfony\Component\HttpFoundation\Request;

class ViewController extends Controller
{
	public function viewAction()
	{
		if(!$this->get('security.context')->isGranted('ROLE_USER')) {
			throw new AccessDeniedException();
		}

		$allOwners = array();

		if($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')){

			$properties = $this->getDoctrine()
							   ->getRepository('ReservableActivityBundle:Activity')
							   ->findAll();

			$result = $this->getDoctrine()
						   ->getManager()
						   ->createQuery('SELECT u.email, u.id
										  FROM ReservableActivityBundle:Activity a
										  INNER JOIN UserUserBundle:Users u 
										  WHERE u.id = a.ownerID
										  GROUP BY u.id')
						   ->getResult();

			foreach($result as $oneResult){
				$allOwners[$oneResult['id']]['email'] = $oneResult['email'];
				$allOwners[$oneResult['id']]['ownerID'] = $oneResult['id'];
			}
		}
		else{
			$ownerID = $this->get('security.context')->getToken()->getUser()->getId();	

			$properties = $this->getDoctrine()
							   ->getRepository('ReservableActivityBundle:Activity')
							   ->findAllByOwnerID($ownerID);
		}

		$arrayPictures = array();
		if(!empty($properties)){
			foreach($properties as $oneResult){
				$allImage = $this->getDoctrine()
								   ->getRepository('ReservableActivityBundle:Picture')
								   ->findAllByPropertyID($oneResult->getId());

				if(!empty($allImage)){
                    $auxImages = array();
                    foreach($allImage as $oneImage) {
                        $auxImages[] = $oneImage['path'];
                    }
                    $arrayPictures[$oneResult->getId()] = $auxImages;
                }
                else{
                    $arrayPictures[$oneResult->getId()][] = 'no-photo.jpg';
                }
			}
		}

		return $this->render('ReservableActivityBundle:View:viewActivities.html.twig', 
			array('properties' => $properties, 'pictures' => $arrayPictures, 'allOwners' => $allOwners));

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

	public function viewDetailsAction($property, Request $request){

		$session = $request->getSession();

		$details = $this->getDoctrine()
						->getRepository('ReservableActivityBundle:Activity')
						->findByPropertyID($property);

		$pictures = $this->getDoctrine()
						 ->getRepository('ReservableActivityBundle:Picture')
					     ->findAllByPropertyID($property);

		$arrayPictures = array();
		foreach($pictures as $onePicture){
			$arrayPictures[] = $onePicture['path'];
		}

        // tipos
        $types = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:TypeActivity')
            ->getAllTypes($details->getTypeRent());

        $typeSelected = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:ActivityyToType')
            ->getTypeSelected($property);

        if($typeSelected){
            foreach($types as $key => $oneType){
                if($oneType['id'] == $typeSelected){
                    $types[$key]['selected'] = 1;
                }
            }
        }

        // features
        $features = array();
        if($typeSelected) {
            $features = $this->getAllFeaturesByType($typeSelected);

            $featuresSelected = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:ActivityToFeature')
                ->getAllFeatures($details->getId());

            if($featuresSelected){
                foreach($features as $key => $oneFeature){
                    if(in_array($oneFeature['id'], $featuresSelected)){
                        $features[$key]['selected'] = 1;
                    }
                }
            }
        }

		return $this->render('ReservableActivityBundle:View:detailsProperty.html.twig', 
			array('details' => $details, 'pictures' => $arrayPictures, 'type' => $types, 'features' => $features));
	}

    private function getAllFeaturesByType($type){
        $features = $this->getDoctrine()
            ->getManager()
            ->createQuery("SELECT f.id, f.name
                           FROM ReservableActivityBundle:TypeToFeature ttf
                           INNER JOIN ReservableActivityBundle:Features f
                           WHERE ttf.featureID = f.id AND ttf.typeID = " . $type)
            ->getResult();

        return $features;
    }
}