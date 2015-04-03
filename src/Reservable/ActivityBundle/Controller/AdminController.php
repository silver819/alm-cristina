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
}