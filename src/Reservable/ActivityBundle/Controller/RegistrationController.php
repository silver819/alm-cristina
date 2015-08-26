<?php

namespace Reservable\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Activity;
use Reservable\ActivityBundle\Form\Type\ActivityType;
use Reservable\ActivityBundle\Entity\Picture;
use Reservable\ActivityBundle\Entity\ActivityToFeature;

class RegistrationController extends Controller
{
    public function newAction()
	{
		// crea una task y le asigna algunos datos ficticios para este ejemplo
        $activity= new Activity();
        $activity->setOwnerID($this->get('security.context')->getToken()->getUser()->getId());
        $activity->setActive(1);

        $picture1 = new Picture();
        $picture2 = new Picture();
        $picture3 = new Picture();
        $picture4 = new Picture();
        $picture5 = new Picture();
        $picture6 = new Picture();
        $activity->getPictures()->add($picture1);
        $activity->getPictures()->add($picture2);
        $activity->getPictures()->add($picture3);
        $activity->getPictures()->add($picture4);
        $activity->getPictures()->add($picture5);
        $activity->getPictures()->add($picture6);

        $form = $this->createForm(new ActivityType(), $activity);

        return $this->render('ReservableActivityBundle:Registration:registerActivity.html.twig', 
        	array('form' => $form->createView(),
        ));
    }

    public function registerActivityAction(){

        $dm = $this->getDoctrine()->getManager();
        $activity = new Activity();
        $form = $this->createForm(new ActivityType(), $activity);
        $form->bind($this->getRequest());

        if ($form->isValid()) {
            $property = $form->getData();
            $dm->persist($property);

            $lastActivityID = $this->getDoctrine()
                                   ->getRepository('ReservableActivityBundle:Activity')
                                   ->getLastActivityID();

            $thisActivityID = $lastActivityID[0][1] + 1;

            $images = $property->getPictures();
            if(!empty($images)){
                $cont = 0;
                foreach($images as $oneImage){
                    $fileName = $thisActivityID . "_" . $cont;
                    $oneImage->setActivityID($thisActivityID);
                    $oneImage->upload($fileName);
                    if($oneImage->getPath()){
                        $dm->persist($oneImage);
                        $cont++;
                    }
                }
            }

            // Si no es administrados, le añadimos el rol
            if(!$this->get('security.context')->getToken()->getUser()->hasRole('ROLE_ADMIN')){
                $this->get('security.context')->getToken()->getUser()->setRoles(array('ROLE_ADMIN'));
                $dm->persist($this->get('security.context')->getToken()->getUser());
            }

            $dm->flush();
            // Envio correo electronico test
            $mensaje = new \Swift_Message();
            $text = '<h1>Nueva actividad registrada</h1><br/><br/>';
            $text .= '<strong>Nombre</strong>: ' . $property->getName();
            $text .= '<br/><strong>Precio</strong>: ' . $property->getPrice() . ' €';
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

    public function newFeatureAction(){

        // tipos
        $types = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:TypeActivity')
            ->getAllTypes();

        // features
        $typesFeatures = array();
        foreach($types as $oneType) {
            $typesFeatures[$oneType['id']]['features'] = $this->getAllFeaturesByType($oneType['id']);
        }

        $allFeatures = $this->getDoctrine()->getRepository('ReservableActivityBundle:Features')->getAllFeatures();

        //ldd($typesFeatures);

        return $this->render('ReservableActivityBundle:Registration:newFeatureForm.html.twig',
            array('types'=> $types , 'typesFeatures' => $typesFeatures, 'features' => $allFeatures));
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

    public function adminTypesAction(){

        // tipos
        $types = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:TypeActivity')
            ->getAllTypes();

        return $this->render('ReservableActivityBundle:Registration:adminTypes.html.twig',
            array('types' => $types)
        );
    }

    public function adminFeaturesAction(){

        return $this->render('ReservableActivityBundle:Registration:adminFeatures.html.twig',
            array()
        );
    }
}
