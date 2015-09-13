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
        $chartOptions = array(
            'allowPointSelect'  => true,
            'cursor'            => 'pointer',
            'dataLabels'        => array('enabled' => false),
            'showInLegend'      => true
        );

        $chartTop5hourlyBooked = new Highchart();
        $chartTop5hourlyBooked->chart->renderTo('top5hourlyBookingsProperties');
        $chartTop5hourlyBooked->title->text('Por hora');
        $chartTop5hourlyBooked->plotOptions->pie($chartOptions);

        $chartTop5dailyBooked = new Highchart();
        $chartTop5dailyBooked->chart->renderTo('top5dailyBookingsProperties');
        $chartTop5dailyBooked->title->text('Por día');
        $chartTop5dailyBooked->plotOptions->pie($chartOptions);

        $data1 = array();
        $data2 = array();
        foreach($top5bookings['hour'] as $property) $data1[] = array($property['propertyName'] . ', ' . $property['ownerName'] . ' ' . $property['ownerSurname'] . ' (' . $property['ownerEmail'] . ' )', (int)$property['numBookings']);
        foreach($top5bookings['day'] as $property)  $data2[] = array($property['propertyName'] . ', ' . $property['ownerName'] . ' ' . $property['ownerSurname'] . ' (' . $property['ownerEmail'] . ' )', (int)$property['numBookings']);


        $chartTop5hourlyBooked->series(array(array('type' => 'pie','name' => 'Reservas', 'data' => $data1)));
        $chartTop5dailyBooked->series(array(array('type' => 'pie','name' => 'Reservas', 'data' => $data2)));

        ## Alojamientos mejor valorados (por día y por hora)
        $top5ratings = $this->getTop5Ratings();

        $xAxisHourlyRated = $xAxisDailyRated = array();
        $yAxisHourlyRated = $yAxisDailyRated = array();
        foreach($top5ratings['hour'] as $rating){
            $xAxisHourlyRated[] = $rating['propertyName'];
            $yAxisHourlyRated[] = array($rating['propertyName'], $rating['meanRating']);
        }
        foreach($top5ratings['day'] as $rating){
            $xAxisDailyRated[] = $rating['propertyName'];
            $yAxisDailyRated[] = array($rating['propertyName'], $rating['meanRating']);
        }

        $chartTop5hourlyRated = new Highchart();
        $chartTop5hourlyRated->chart->renderTo('top5hourlyRatedProperties');
        $chartTop5hourlyRated->chart->type('column');
        $chartTop5hourlyRated->title->text('Resultados de la encuesta sobre 5 puntos');
        $chartTop5hourlyRated->plotOptions->pie($chartOptions);
        $chartTop5hourlyRated->xAxis->categories($xAxisHourlyRated);
        $chartTop5hourlyRated->series(array(array('type' => 'column', 'name' => 'Valoración media', 'data' => $yAxisHourlyRated)));

        $chartTop5dailyRated = new Highchart();
        $chartTop5dailyRated->chart->renderTo('top5dailyRatedProperties');
        $chartTop5dailyRated->chart->type('column');
        $chartTop5dailyRated->title->text('Resultados de la encuesta sobre 5 puntos');
        $chartTop5dailyRated->plotOptions->pie($chartOptions);
        $chartTop5dailyRated->xAxis->categories($xAxisDailyRated);
        $chartTop5dailyRated->series(array(array('type' => 'column', 'name' => 'Valoración media', 'data' => $yAxisDailyRated)));

        ## Clientes más activos


        return $this->render(
            'RagingsRatingBundle:Statistics:index.html.twig',
            array(
                'chartTop5hourlyBooked' => $chartTop5hourlyBooked,
                'chartTop5dailyBooked'  => $chartTop5dailyBooked,
                'chartTop5hourlyRated'  => $chartTop5hourlyRated,
                'chartTop5dailyRated'   => $chartTop5dailyRated
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

    public function getTop5Ratings(){

        $arrayReturn = array();

        $results = $this->getDoctrine()
                ->getManager()
                ->createQuery('SELECT count(r.id) as numRatings, SUM(r.ubicacion) as ubicacionCount,
                               SUM(r.llegar) as llegarCount, SUM(r.limpieza) as limpiezaCount,
                               SUM(r.material) as materialCount, SUM(r.caracteristicas) as caracteristicasCount,
                               SUM(r.gestiones) as gestionesCount, SUM(r.usabilidad) as usabilidadCount,
                               b.activityID
                               FROM RagingsRatingBundle:Rating r
                               INNER JOIN BookingsBookingBundle:Booking b
                               WHERE b.id = r.reservationNumber
                               GROUP BY b.activityID')
                ->getResult();

        //ldd($results);

        if(!empty($results)){
            foreach($results as $one){

                $property = $this->getDoctrine()
                    ->getRepository('ReservableActivityBundle:Activity')
                    ->findByPropertyID($one['activityID']);

                if(!isset($arrayReturn[$property->getTypeRent()]) || count($arrayReturn[$property->getTypeRent()]) <= 5) {

                    $aux = array();

                    $aux['propertyName']    = $property->getName();
                    $aux['numRatings']      = $one['numRatings'];
                    $aux['meanRating']      = ($one['ubicacionCount'] + $one['llegarCount'] + $one['limpiezaCount'] + $one['materialCount'] + $one['caracteristicasCount'] + $one['gestionesCount'] + $one['usabilidadCount']) / (7 * $one['numRatings']);

                    $arrayReturn[$property->getTypeRent()][] = $aux;
                }
            }
        }

        //ldd($arrayReturn);

        return $arrayReturn;
    }
}
