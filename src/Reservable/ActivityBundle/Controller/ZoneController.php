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
                        $provinces[$province->getId()] = array('name' => $province->getName(), 'cities' => $cities, 'numProperties' => $this->countPropiertiesFromCities($cities));
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

}