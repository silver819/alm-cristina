<?php

namespace Reservable\ActivityBundle\Controller;

use Reservable\ActivityBundle\Entity\ActivityyToType;
use Reservable\ActivityBundle\Entity\ActivityToFeature;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Activity;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
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

        $type = '';
        if($typeSelected){
            foreach($types as $key => $oneType){
                if($oneType['id'] == $typeSelected){
                    $type = $oneType['name'];
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
//ladybug_dump($details);
        return $this->render('ReservableActivityBundle:Admin:adminDetailsProperty.html.twig',
            array('details' => $details, 'pictures' => $arrayPictures, 'type' => $type, 'features' => $features));
    }

    public function modifDetailsAction($property, Request $request){
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

        return $this->render('ReservableActivityBundle:Admin:modifDetailsProperty.html.twig',
            array('details' => $details, 'pictures' => $arrayPictures, 'types' => $types, 'features' => $features));
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

    public function saveModifDetailsAction(){

        if($_POST['productID']){
            // Guardar
            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("UPDATE ReservableActivityBundle:Activity a
                               SET   a.name = '" . $_POST['name'] . "',
                                     a.price = '" . $_POST['price'] . "',
                                     a.address = '" . $_POST['address'] . "',
                                     a.description = '" . $_POST['description'] . "'
                               WHERE a.id = '" . $_POST['productID'] . "'")
                ->getResult();

            //tipos y features
            $em = $this->getDoctrine()->getManager();
            if(isset($_POST['type'])){
                $resultQuery = $this->getDoctrine()
                    ->getManager()
                    ->createQuery("DELETE FROM ReservableActivityBundle:ActivityyToType att
                                       WHERE att.activityID = " . $_POST['productID'])
                    ->getResult();

                $activityTypeEquivalence = new ActivityyToType();
                $activityTypeEquivalence->setActivityID($_POST['productID']);
                $activityTypeEquivalence->setTypeID($_POST['type']);

                $em->persist($activityTypeEquivalence);

            }

            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("DELETE FROM ReservableActivityBundle:ActivityToFeature atf
                                       WHERE atf.activityID = " . $_POST['productID'])
                ->getResult();
            if(isset($_POST['feature']) && !empty($_POST['feature'])) {
                foreach ($_POST['feature'] as $oneFeature) {
                    $activityFeatureEquivalence = new ActivityToFeature();
                    $activityFeatureEquivalence->setActivityID($_POST['productID']);
                    $activityFeatureEquivalence->setFeatureID($oneFeature);

                    $em->persist($activityFeatureEquivalence);
                }
            }

            $em->flush();

            // Recopilo info
            $details = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:Activity')
                ->findByPropertyID($_POST['productID']);

            $pictures = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:Picture')
                ->findAllByPropertyID($_POST['productID']);

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
                ->getTypeSelected($_POST['productID']);

            $type = '';
            if($typeSelected){
                foreach($types as $key => $oneType){
                    if($oneType['id'] == $typeSelected){
                        $type = $oneType['name'];
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

            return $this->render('ReservableActivityBundle:Admin:adminDetailsProperty.html.twig',
                array('details' => $details, 'pictures' => $arrayPictures , 'type' => $type, 'features' => $features ));
        }
    }

    public function modifActiveAction($property){
        if($property){
            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("UPDATE ReservableActivityBundle:Activity a
                               SET   a.active = 1
                               WHERE a.id = '" . $property . "'")
                ->getResult();

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

            return $this->render('ReservableActivityBundle:Admin:adminDetailsProperty.html.twig',
                array('details' => $details, 'pictures' => $arrayPictures));
        }
    }

    public function modifDeactiveAction($property){
        if($property){
            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("UPDATE ReservableActivityBundle:Activity a
                               SET   a.active = 0
                               WHERE a.id = '" . $property . "'")
                ->getResult();

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

            return $this->render('ReservableActivityBundle:Admin:adminDetailsProperty.html.twig',
                array('details' => $details, 'pictures' => $arrayPictures));
        }
    }

    public function modifDeleteAction($property){
        if($property){
            if($this->getDoctrine()
                ->getRepository('ReservableActivityBundle:Activity')
                ->deleteProperty($property)){

                $this->getDoctrine()
                    ->getRepository('ReservableActivityBundle:Picture')
                    ->deleteAllByPropertyID($property);

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
                        $firstImage = $this->getDoctrine()
                            ->getRepository('ReservableActivityBundle:Picture')
                            ->findAllByPropertyID($oneResult->getId());

                        if(!empty($firstImage[0]['path'])){
                            $arrayPictures[$oneResult->getId()] = $firstImage[0]['path'];
                        }
                    }
                }

                return $this->render('ReservableActivityBundle:View:viewActivities.html.twig',
                    array('properties' => $properties, 'pictures' => $arrayPictures, 'allOwners' => $allOwners));

            }
        }
    }
}