<?php

namespace Reservable\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Activity;

class DefaultController extends Controller
{
	public function homepageAction(){
		return $this->render('ReservableActivityBundle:Default:index.html.twig');
	}

    public function indexAction($name)
	{
		$product = new Activity();
	    $product->setName('Test');
	    $product->setPrice('19.99');
	    $product->setOwnerID(1);
	    $product->setTypeRent('hora');
	    $product->setAddress('Direccion');
	    $product->setLat(30.3446352);
	    $product->setLng(64.3425425);
	    $product->setDescription("Descripcion");
	    $product->setActive(1);

	    $em = $this->getDoctrine()->getManager();
	    $em->persist($product);
	    $em->flush();

        return $this->render('ReservableActivityBundle:Default:index.html.twig', array('name' => $name));
    }
}
