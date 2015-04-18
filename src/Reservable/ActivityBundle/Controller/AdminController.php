<?php

namespace Reservable\ActivityBundle\Controller;

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

        return $this->render('ReservableActivityBundle:Admin:adminDetailsProperty.html.twig',
            array('details' => $details, 'pictures' => $arrayPictures));
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

        return $this->render('ReservableActivityBundle:Admin:modifDetailsProperty.html.twig',
            array('details' => $details, 'pictures' => $arrayPictures));
    }

    public function saveModifDetailsAction(){
        if($_POST['productID']){
            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("UPDATE ReservableActivityBundle:Activity a
                               SET   a.name = '" . $_POST['name'] . "',
                                     a.price = '" . $_POST['price'] . "',
                                     a.address = '" . $_POST['address'] . "'
                               WHERE a.id = '" . $_POST['productID'] . "'")
                ->getResult();

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

            return $this->render('ReservableActivityBundle:Admin:adminDetailsProperty.html.twig',
                array('details' => $details, 'pictures' => $arrayPictures));
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