<?php

namespace Reservable\ActivityBundle\Controller;

use Reservable\ActivityBundle\Entity\TypeActivity;
use Reservable\ActivityBundle\Entity\TypeToFeature;
use Reservable\ActivityBundle\Entity\Features;
use Reservable\ActivityBundle\Entity\Zone;
use Reservable\ActivityBundle\Entity\ActivityyToType;
use Reservable\ActivityBundle\Entity\ActivityToFeature;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Seasons;
use Symfony\Component\HttpFoundation\Request;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Component\HttpFoundation\JsonResponse;

class ZoneController extends Controller
{

    public function adminZoneAction()
    {
        $zoneTypeResults = $this->getDoctrine()->getRepository('ReservableActivityBundle:ZoneType')->findAll();
        $zoneTypes = array();
        foreach($zoneTypeResults as $thisZoneType){$zoneTypes[$thisZoneType->getId()] = $thisZoneType->getName();}


        // Continentes
        $arrayContinents = $this->getDoctrine()->getRepository('ReservableActivityBundle:Zone')->findBy(array('fatherZone' => 0));
        $continents = array();
        foreach($arrayContinents as $continent){
            // Paises
            $arrayCountries = $this->getDoctrine()->getRepository('ReservableActivityBundle:Zone')->findBy(array('fatherZone' => $continent->getId()));
            $countries = array();
            foreach($arrayCountries as $country){
                // Comunidades
                $arrayComunities = $this->getDoctrine()->getRepository('ReservableActivityBundle:Zone')->findBy(array('fatherZone' => $country->getId()));
                $comunities = array();
                foreach($arrayComunities as $comunity) {
                    // Provincias
                    $arrayProvinces = $this->getDoctrine()->getRepository('ReservableActivityBundle:Zone')->findBy(array('fatherZone' => $comunity->getId()));
                    $provinces = array();
                    foreach($arrayProvinces as $province) {
                        // Ciudades
                        $arrayCities = $this->getDoctrine()->getRepository('ReservableActivityBundle:Zone')->findBy(array('fatherZone' => $province->getId()));
                        $cities = array();
                        foreach($arrayCities as $city) {
                            $numProperties  = count($this->getDoctrine()->getRepository('ReservableActivityBundle:Activity')->findBy(array('zone' => $city->getId())));
                            $cities[$city->getId()] = array('id' => $city->getId(), 'name' => $city->getName(), 'numProperties' => $numProperties);
                        }
                        $provinces[$province->getId()] = array('name' => $province->getName(), 'id' => $province->getId(), 'cities' => $cities, 'numProperties' => $this->countPropiertiesFromCities($cities));
                    }
                    $comunities[$comunity->getId()] = array('name' => $comunity->getName(), 'provinces' => $provinces, 'numProperties' => $this->countPropiertiesFromCities($provinces));
                }
                $countries[$country->getId()] = array('name' => $country->getName(), 'comunities' => $comunities, 'numProperties' => $this->countPropiertiesFromCities($comunities));
            }
            $continents[$continent->getId()] = array('name' => $continent->getName(), 'countries' => $countries, 'numProperties' => $this->countPropiertiesFromCities($countries));
        }
//ldd($continents);
        return $this->render('ReservableActivityBundle:Zone:adminZone.html.twig',
            array('zoneTypes' => $zoneTypes, 'zonesTree' => $continents));
    }

    private function countPropiertiesFromCities($cities){

        $total = 0;
        foreach($cities as $city){
            $total += $city['numProperties'];
        }
        return $total;
    }

    public function adminAddCityAction(){

        $return = array();

        if($_POST['cityName'] && $_POST['provinceID']){
            $newCity = new Zone();
            $newCity->setName($_POST['cityName']);
            $newCity->setFatherZone($_POST['provinceID']);
            $newCity->setType(5);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newCity);
            $em->flush();

            $return['cityName'] = $_POST['cityName'];
            $return['provinceID'] = $_POST['provinceID'];
            $return['type'] = 5;
        }

        return new JsonResponse($return);
    }

    public function adminDeleleZoneAction(){
        $return = array();

        if($_POST['name'] && $_POST['type']){
            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("DELETE FROM ReservableActivityBundle:Zone z
                               WHERE z.name LIKE '" . $_POST['name'] . "' AND z.type = " . $_POST['type'])
                ->getResult();

            $return['name'] = $_POST['name'];
            $return['type'] = $_POST['type'];
        }

        return new JsonResponse($return);
    }

}