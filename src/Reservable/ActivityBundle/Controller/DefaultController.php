<?php

namespace Reservable\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Activity;

class DefaultController extends Controller
{
	public function homepageAction(){
        // Calculamos las mejores propiedades

        $top5 = $this->getTop5Ratings();

        //ldd($top5);

		//return $this->render('ReservableActivityBundle:Default:index.html.twig', array('top5', $top5));
		return $this->render('ReservableActivityBundle:Search:displayIndex.html.twig', array('top5' => $top5));
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

                if(!isset($arrayReturn[$property->getTypeRent()]) || count($arrayReturn[$property->getTypeRent()]) <= 10) {

                    $aux = array();

                    $aux['propertyName']    = $property->getName();
                    $aux['numRatings']      = $one['numRatings'];
                    $aux['priceSince']      = $this->getDoctrine()
                        ->getManager()
                        ->createQuery('SELECT MIN(s.price)
                               FROM ReservableActivityBundle:Seasons s
                               WHERE s.activityID = ' . $one['activityID'] . ' AND s.endSeason > ' . date('Ymd'))
                        ->getResult()[0][1];
                    $aux['numComments']     = $this->getDoctrine()
                        ->getManager()
                        ->createQuery('SELECT count(r.comentarios)
                               FROM RagingsRatingBundle:Rating r
                               INNER JOIN BookingsBookingBundle:Booking b
                               WHERE b.id = r.reservationNumber
                               AND b.activityID = ' . $one['activityID'] . ' AND r.comentarios IS NOT NULL')
                        ->getResult()[0][1];
                    $aux['meanRating']      = round(($one['ubicacionCount'] +
                                $one['llegarCount'] +
                                $one['limpiezaCount'] +
                                $one['materialCount'] +
                                $one['caracteristicasCount'] +
                                $one['gestionesCount'] +
                                $one['usabilidadCount']) / (7 * $one['numRatings']), 2) * 2;

                    $aux['description'] = $this->getDoctrine()
                        ->getManager()
                        ->createQuery('SELECT MIN(a.description)
                               FROM ReservableActivityBundle:Activity a
                               WHERE a.id = ' . $one['activityID'])
                        ->getResult()[0][1];

                    $allImage = $this->getDoctrine()
                        ->getRepository('ReservableActivityBundle:Picture')
                        ->findAllByPropertyID($one['activityID']);

                    $auxImages = array();

                    if(!empty($allImage)) {
                        foreach ($allImage as $oneImage) {
                            $auxImages[] = $oneImage['path'];
                        }
                    }
                    else{
                        $auxImages[] = 'no-photo.jpg';
                    }

                    $aux['images'] = $auxImages;



                    $arrayReturn[$property->getTypeRent()][] = $aux;
                }
            }
        }

        //ldd($arrayReturn);

        return $arrayReturn;
    }
}
