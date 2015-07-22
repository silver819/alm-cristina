<?php

namespace Ragings\RatingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Ragings\RatingBundle\Entity\Rating;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('RagingsRatingBundle:Default:index.html.twig', array('name' => $name));
    }

    public function makeQuestionaireAction($reservationNumber){

        return $this->render(
            'RagingsRatingBundle:Default:makeQuestionaire.html.twig',
            array('reservationNumber' => $reservationNumber));
    }

    public function saveQuestionaireAction(Request $request){

        $rating = new Rating;

        $rating->setReservationNumber($request->request->get('reservationNumber'));
        $rating->setUbicacion($request->request->get('ubicacion'));
        $rating->setLlegar($request->request->get('llegar'));
        $rating->setLimpieza($request->request->get('limpieza'));
        $rating->setMaterial($request->request->get('material'));
        $rating->setCaracteristicas($request->request->get('caracteristicas'));
        $rating->setGestiones($request->request->get('gestiones'));
        $rating->setUsabilidad($request->request->get('usabilidad'));
        $rating->setRepetir($request->request->get('repetir'));
        $rating->setEncontrar($request->request->get('encontrar'));
        $rating->setRecomendar($request->request->get('recomendar'));
        $rating->setMejoras($request->request->get('mejoras'));
        $rating->setComentarios($request->request->get('comentarios'));

        $dm = $this->getDoctrine()->getManager();
        $dm->persist($rating);
        $dm->flush();

        return $this->render(
            'RagingsRatingBundle:Default:thanks.html.twig');

    }
}
