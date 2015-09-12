<?php

namespace Ragings\RatingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Ragings\RatingBundle\Entity\Rating;
use Ob\HighchartsBundle\Highcharts\Highchart;

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

    public function statisticsAction(){

        ## Propiedades más reservados (por día y por hora)
        $top5bookings = $this->getTop5Bookings();
        //ldd($top5bookings);

        // Chart
        $pieOptions = array(
            'allowPointSelect'  => true,
            'cursor'    => 'pointer',
            'dataLabels'    => array('enabled' => false),
            'showInLegend'  => true
        );

        $ob1 = new Highchart();
        $ob1->chart->renderTo('top5dailyBookingsProperties');
        $ob1->title->text('Top 5 propiedades más reservadas por hora');
        $ob1->plotOptions->pie($pieOptions);
        $data = array();
        foreach($top5bookings['day'] as $property){
            $data[] = array(
                $property['propertyName'] . ', ' . $property['ownerName'] . ' ' . $property['ownerSurname'] . ' (' . $property['ownerEmail'] . ' )',
                (int)$property['numBookings']);
        }

        $ob1->series(array(array('type' => 'pie','name' => 'Reservas', 'data' => $data)));

        ## Alojamientos mejor valorados (por día y por hora)

        ## Clientes más activos


        return $this->render(
            'RagingsRatingBundle:Statistics:index.html.twig',
            array(
                'chart' => $ob1
            ));

    }

    public function getTop5Bookings(){

        $arrayReturn = array();

        $results = $this->getDoctrine()
                ->getManager()
                ->createQuery('SELECT count(b.id) as numBookings, a.id, a.name, a.typeRent, a.ownerID
                               FROM BookingsBookingBundle:Booking b
                               INNER JOIN ReservableActivityBundle:Activity a
                               WHERE b.activityID = a.id
                               GROUP BY b.activityID
                               ORDER BY numBookings DESC')
                ->getResult();

        //ldd($results);

        if(!empty($results)){
            foreach($results as $one){

                if(!isset($arrayReturn[$one['typeRent']]) || count($arrayReturn[$one['typeRent']]) <= 5) {

                    $ownerData = $this->getDoctrine()
                        ->getRepository('UserUserBundle:Users')
                        ->getUserByUserID($one['ownerID']);

                    $aux = array();

                    $aux['numBookings']     = $one['numBookings'];
                    $aux['propertyId']      = $one['id'];
                    $aux['propertyName']    = $one['name'];
                    $aux['ownerEmail']      = $ownerData->getEmail();
                    $aux['ownerName']       = $ownerData->getName();
                    $aux['ownerSurname']    = $ownerData->getSurname();

                    $arrayReturn[$one['typeRent']][] = $aux;
                }
            }
        }

        //ldd($arrayReturn);

        return $arrayReturn;
    }
}
