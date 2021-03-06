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

        // Ciudades
        $cities = $this->getDoctrine()->getRepository("ReservableActivityBundle:Zone")->findBy(array("type" => 5));
        $cityNames = array();
        foreach($cities as $city){
            $cityNames[$city->getId()]['name'] = $city->getName();
            $cityNames[$city->getId()]['id'] = $city->getId();
        }

        // Temporadas y precio mínimo
        $seasons    = array();
        $today      = date('Ymd');
        $arrayMinPriceByProperty = array();
        $arrayNumComments = array();
        $arrayRatings = array();
        foreach($properties as $property){

            $seasons[$property->getId()] = array('date' => 0, 'start' => 22, 'end' => 0);
            $arrayMinPriceByProperty[$property->getId()] = 999999;

            $seasonsProperty = $this->getDoctrine()->getRepository('ReservableActivityBundle:Seasons')->findBy(array('activityID' => $property->getId()));

            if(empty($seasonsProperty)){
                $arrayMinPriceByProperty[$property->getId()] = '-';
                continue;
            }

            if($property->getTypeRent() == 'hour'){

                foreach($seasonsProperty as $season){

                    $seasons[$property->getId()]['date'] = 1;

                    if((int)$season->getStartSeason() < (int)$seasons[$property->getId()]['start']){
                        $seasons[$property->getId()]['start'] = $season->getStartSeason();
                    }
                    if((int)$season->getEndSeason() > (int)$seasons[$property->getId()]['end']){
                        $seasons[$property->getId()]['end'] = $season->getEndSeason();
                    }
                }
            }
            else{

                foreach($seasonsProperty as $season){

                    if((int)$season->getEndSeason() > $today && $seasons[$property->getId()]['date'] < $season->getEndSeason()){
                        $seasons[$property->getId()] = array('date' => $season->getEndSeason(), 'twig' => substr($season->getEndSeason(), 6,2) . '/' . substr($season->getEndSeason(), 4,2) . '/' . substr($season->getEndSeason(), 0,4));
                    }
                }
            }

            // Precio minimo
            if($season->getPrice() < $arrayMinPriceByProperty[$property->getId()]){
                $arrayMinPriceByProperty[$property->getId()] = $season->getPrice();
            }

            // Comentarios
            $comments       = $this->getComments($property->getId());
            $arrayNumComments[$property->getId()] = '-';
            if(count($comments) > 0) $arrayNumComments[$property->getId()] = count(($comments));

            // Valoracion media
            $arrayRatings[$property->getId()] = '-';
            $resultRatings  = $this->getRatings($property->getId());
            if($resultRatings['totalScore'] > 0) $arrayRatings[$property->getId()] = $resultRatings['totalScore'] . " / 5";
        }

		return $this->render('ReservableActivityBundle:View:viewActivities.html.twig', 
			array(
                  'properties'              => $properties,
                  'pictures'                => $arrayPictures,
                  'allOwners'               => $allOwners,
                  'cityNames'               => $cityNames,
                  'seasonsByProperty'       => $seasons,
                  'arrayMinPriceByProperty' => $arrayMinPriceByProperty,
                  'arrayNumComments'        => $arrayNumComments,
                  'arrayRatings'            => $arrayRatings
            )
        );

    }

    public function activePropertyAction(){
		if($_POST['productID']){
			if($this->getDoctrine()
					->getRepository('ReservableActivityBundle:Activity')
					->activeProperty($_POST['productID'])){
				
				return $this->redirect('admin/view-properties');
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
				
				return $this->redirect('admin/view-properties');
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

		return $this->redirect('admin/view-properties');
	}

    public function deletePropertyAction(){
    	if($_POST['productID']){
			if($this->getDoctrine()
					->getRepository('ReservableActivityBundle:Activity')
					->deleteProperty($_POST['productID'])){

				$this->getDoctrine()
					->getRepository('ReservableActivityBundle:Picture')
					->deleteAllByPropertyID($_POST['productID']);
				
				return $this->redirect('admin/view-properties');
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

    public function getComments($propertyID)
    {

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
}