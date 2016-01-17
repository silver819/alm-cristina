<?php

namespace Reservable\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Activity;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
	public function homepageAction(){
        // Calculamos las mejores propiedades

        $filters    = $this->getFilters();
        $top5       = $this->getTop5Ratings();

        $arrayCitiesQuery = $this->getDoctrine()->getRepository('ReservableActivityBundle:Zone')->findBy(array('type' => 4), array('name' => 'ASC'));
        $cities = array();
        foreach($arrayCitiesQuery as $city){
            $childrens = $this->getDoctrine()->getRepository('ReservableActivityBundle:Zone')->findBy(array('type' => 5, 'fatherZone' => $city->getId()), array('name' => 'ASC'));
            $arrayChildrens = array();
            if($childrens) {
                foreach ($childrens as $children) {
                    $arrayChildrens[] = array('id' => $children->getId(), 'name' => $children->getName());
                }
            }
            $cities[] = array('id' => $city->getId(), 'name' => $city->getName(), 'childrens' => $arrayChildrens);
        }

        //ldd($cities);

		return $this->render('ReservableActivityBundle:Search:displayIndex.html.twig',
            array('cities' => $cities, 'filters'=> $filters, 'top5' => $top5));
	}

    public function changeCityAction(){
        //$_POST['provinceID'] = 4;
        //ld($_POST);
        if (isset($_POST['provinceID'])) {
            $childrens = $this->getDoctrine()->getRepository('ReservableActivityBundle:Zone')->findBy(array('type' => 5, 'fatherZone' => $_POST['provinceID']), array('name' => 'ASC'));
            $arrayChildrens = array();
            if($childrens) {
                foreach ($childrens as $children) {
                    $arrayChildrens[] = array('id' => $children->getId(), 'name' => $children->getName());
                }
            }
            //ldd($arrayChildrens);
            return new JsonResponse(array('childrens' => $arrayChildrens));
        }
        else
            return new JsonResponse(array());
    }

    public function getFilters(){
        $arrayReturn = array();

        $locale = $this->get('request')->getLocale();

        $results = $this->getDoctrine()
            ->getManager()
            ->createQuery('SELECT t.id, t.name, t.mode, t.' . $locale . ' FROM ReservableActivityBundle:TypeActivity t')
            ->getResult();

        foreach($results as $result){
            $arrayReturn[$result['mode']][] = array('id' => $result['id'], 'name' => $result[$locale]);
        }

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

                if(!isset($arrayReturn[$property->getTypeRent()]) || count($arrayReturn[$property->getTypeRent()]) <= 10) {

                    $aux = array();

                    $aux['propertyName']    = $property->getName();
                    $aux['numRatings']      = $one['numRatings'];
                    if($property->getTypeRent() == 'day') {
                        $aux['priceSince'] = $this->getDoctrine()
                            ->getManager()
                            ->createQuery('SELECT MIN(s.price)
                               FROM ReservableActivityBundle:Seasons s
                               WHERE s.activityID = ' . $one['activityID'] . ' AND s.endSeason > ' . date('Ymd'))
                            ->getResult()[0][1];
                    }
                    else{
                        $aux['priceSince'] = $this->getDoctrine()
                            ->getManager()
                            ->createQuery('SELECT MIN(s.price)
                               FROM ReservableActivityBundle:Seasons s
                               WHERE s.activityID = ' . $one['activityID'])
                            ->getResult()[0][1];
                    }
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
