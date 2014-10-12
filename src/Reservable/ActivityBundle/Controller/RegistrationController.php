<?php

namespace Reservable\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Activity;
use Reservable\ActivityBundle\Form\Type\ActivityType;

class RegistrationController extends Controller
{
    public function newAction()
	{
		// crea una task y le asigna algunos datos ficticios para este ejemplo
        $activity= new Activity();
        $activity->setOwnerID($this->get('security.context')->getToken()->getUser()->getId());
        $activity->setActive(1);

        $form = $this->createForm(new ActivityType(), $activity);

        return $this->render('ReservableActivityBundle:Registration:registerActivity.html.twig', 
        	array('form' => $form->createView(),
        ));
    }

    public function registerActivityAction(){

        $dm = $this->getDoctrine()->getManager();
        $form = $this->createForm(new ActivityType());
        $form->bind($this->getRequest());
        
        if ($form->isValid()) {
            $registration = $form->getData();
            $dm->persist($registration);
            $dm->flush();
            // Envio correo electronico test
            $mensaje = new \Swift_Message();
            $text = '<h1>Nueva actividad registrada</h1><br/><br/>';
            $text .= '<strong>Nombre</strong>: ' . $registration->getName();
            $text .= '<br/><strong>Precio</strong>: ' . $registration->getPrice() . ' €';
            $text .= '<br/><br/><p><a href="http://almacen.dev/app_dev.php/view-instalations">Click aquí para ver sus instalaciones</a></p>';
            $userEmail = $this->get('security.context')->getToken()->getUser()->getEmail();;
            $mensaje->setContentType ('text/html')
            ->setSubject ('Nueva instalación en Almacen.Dev')
            ->setFrom('almacenpfcs@gmail.com')
            ->setTo($userEmail)
            ->setBody($text);
            $this->get('mailer')->send($mensaje);

            return $this->redirect('property-registered');
        }
        return $this->render('ReservableActivityBundle:Registration:registerActivity.html.twig', array('form'=>$form->CreateView()));
    }

    public function registeredActivityAction(){
        return $this->render('ReservableActivityBundle:Registration:registrationSuccess.html.twig');
    }
}
