<?php

namespace Reservable\ActivityBundle\Controller;

use Reservable\ActivityBundle\Entity\TypeToFeature;
use Reservable\ActivityBundle\Entity\ActivityyToType;
use Reservable\ActivityBundle\Entity\ActivityToFeature;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Seasons;
use Symfony\Component\HttpFoundation\Request;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminController extends Controller
{
    public function viewDetailsAction($property, Request $request){

        $result = $this->getDataLodging($property, $request);

        return $this->render('ReservableActivityBundle:Admin:adminDetailsProperty.html.twig',
            array(
                'details'       => $result['details'],
                'pictures'      => $result['arrayPictures'],
                'type'          => $result['type'],
                'features'      => $result['features'],
                'comments'      => $result['comments'],
                'ratings'       => $result['ratings'],
                'totalRating'   => $result['totalRating'],
                'chart'         => $result['ob'],
                'seasons'       => $result['seasons'],
                'map'           => $result['map']
            )
        );
    }

    public function modifDetailsAction($property, Request $request){
        $session = $request->getSession();

        $details = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:Activity')
            ->findByPropertyID($property);

        // Mapa
        $lat = $details->getLat();$lng = $details->getLng();

        $marker = $this->get('ivory_google_map.marker');
        $marker->setPosition($lat, $lng);
        //ldd($marker);

        $map = $this->get('ivory_google_map.map');
        $map->setCenter($lat, $lng);
        $map->addMarker($marker);
        $map->setMapOptions(array('zoom' => 13));
        $map->setStylesheetOptions(array('width' => '100%'));
        //ldd($map);

        // Imagenes
        $pictures = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:Picture')
            ->findAllByPropertyID($property);

        $arrayPictures = array();
        foreach($pictures as $onePicture){
            $arrayPictures[] = $onePicture['path'];
        }

        // tipos
        $types = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:TypeActivity')
            ->getAllTypes($details->getTypeRent());

        $typeSelected = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:ActivityyToType')
            ->getTypeSelected($property);

        if($typeSelected){
            foreach($types as $key => $oneType){
                if($oneType['id'] == $typeSelected){
                    $types[$key]['selected'] = 1;
                }
            }
        }

        // features
        $features = array();
        if($typeSelected) {
            $features = $this->getAllFeaturesByType($typeSelected);

            $featuresSelected = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:ActivityToFeature')
                ->getAllFeatures($details->getId());

            if($featuresSelected){
                foreach($features as $key => $oneFeature){
                    if(in_array($oneFeature['id'], $featuresSelected)){
                        $features[$key]['selected'] = 1;
                    }
                }
            }
        }

        // Precios y temporadas
        $seasons = $this->getAllSeasonsByPropertyId($property);

        return $this->render('ReservableActivityBundle:Admin:modifDetailsProperty.html.twig',
            array('details' => $details,
                'map'       => $map,
                'pictures'  => $arrayPictures,
                'types'     => $types,
                'features'  => $features,
                'seasons'   => $seasons));
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

    private function getAllSeasonsByPropertyId($propertyID){

        $today = date('Ymd');

        $seasons = $this->getDoctrine()
            ->getManager()
            ->createQuery("SELECT s.startSeason, s.endSeason, s.price
                           FROM ReservableActivityBundle:Seasons s
                           WHERE s.activityID =  " . $propertyID . "
                           AND s.endSeason > " . $today)
            ->getResult();

        if(!empty($seasons)){
            foreach($seasons as $key => $season){
                $seasons[$key]['startSeason'] = substr($season['startSeason'], 6, 2) . '/' . substr($season['startSeason'], 4, 2) . '/' . substr($season['startSeason'], 0, 4);
                $seasons[$key]['endSeason']   = substr($season['endSeason'], 6, 2) . '/' . substr($season['endSeason'], 4, 2) . '/' . substr($season['endSeason'], 0, 4);
            }
        }

        return $seasons;
    }

    public function saveModifDetailsAction(Request $request){

        $em = $this->getDoctrine()->getManager();

        if($_POST['productID']){
            // Información general
            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("UPDATE ReservableActivityBundle:Activity a
                               SET   a.description = '" . $_POST['description'] . "',
                               a.address = '" . $_POST['address'] . "',
                               a.lat = '" . $_POST['latAddress'] . "',
                               a.lng = '" . $_POST['lngAddress'] . "'
                               WHERE a.id = '" . $_POST['productID'] . "'")
                ->getResult();

            // Temporadas
            foreach($_POST['Seasons'] as $oneSeason){
                if($oneSeason['From'] != '' && $oneSeason['To'] != '' && $oneSeason['Price'] != ''){
                    $season = new Seasons();
                    $season->setActivityID($_POST['productID']);
                    $season->setStartSeason($this->FormatDate($oneSeason['From']));
                    $season->setEndSeason($this->FormatDate($oneSeason['To']));
                    $season->setPrice($oneSeason['Price']);

                    $em->persist($season);
                }
            }

            // Tipos y features
            if(isset($_POST['type'])){
                $resultQuery = $this->getDoctrine()
                    ->getManager()
                    ->createQuery("DELETE FROM ReservableActivityBundle:ActivityyToType att
                                       WHERE att.activityID = " . $_POST['productID'])
                    ->getResult();

                $activityTypeEquivalence = new ActivityyToType();
                $activityTypeEquivalence->setActivityID($_POST['productID']);
                $activityTypeEquivalence->setTypeID($_POST['type']);

                $em->persist($activityTypeEquivalence);

            }

            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("DELETE FROM ReservableActivityBundle:ActivityToFeature atf
                                       WHERE atf.activityID = " . $_POST['productID'])
                ->getResult();
            if(isset($_POST['feature']) && !empty($_POST['feature'])) {
                foreach ($_POST['feature'] as $oneFeature) {
                    $activityFeatureEquivalence = new ActivityToFeature();
                    $activityFeatureEquivalence->setActivityID($_POST['productID']);
                    $activityFeatureEquivalence->setFeatureID($oneFeature);

                    $em->persist($activityFeatureEquivalence);
                }
            }

            $em->flush();

            // ****   RECOPILO INFO   ****
            $result = $this->getDataLodging($_POST['productID'], $request);

            return $this->render('ReservableActivityBundle:Admin:adminDetailsProperty.html.twig',
                array(
                    'details'       => $result['details'],
                    'pictures'      => $result['arrayPictures'],
                    'type'          => $result['type'],
                    'features'      => $result['features'],
                    'comments'      => $result['comments'],
                    'ratings'       => $result['ratings'],
                    'totalRating'   => $result['totalRating'],
                    'chart'         => $result['ob'],
                    'seasons'       => $result['seasons'],
                    'map'           => $result['map']
                )
            );
        }
    }

    public function modifActiveAction($property){
        if($property){
            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("UPDATE ReservableActivityBundle:Activity a
                               SET   a.active = 1
                               WHERE a.id = '" . $property . "'")
                ->getResult();

            $details = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:Activity')
                ->findByPropertyID($property);

            $pictures = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:Picture')
                ->findAllByPropertyID($property);

            $arrayPictures = array();
            foreach($pictures as $onePicture){
                $arrayPictures[] = $onePicture['path'];
            }

            return $this->render('ReservableActivityBundle:Admin:adminDetailsProperty.html.twig',
                array('details' => $details, 'pictures' => $arrayPictures));
        }
    }

    public function modifDeactiveAction($property){
        if($property){
            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("UPDATE ReservableActivityBundle:Activity a
                               SET   a.active = 0
                               WHERE a.id = '" . $property . "'")
                ->getResult();

            $details = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:Activity')
                ->findByPropertyID($property);

            $pictures = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:Picture')
                ->findAllByPropertyID($property);

            $arrayPictures = array();
            foreach($pictures as $onePicture){
                $arrayPictures[] = $onePicture['path'];
            }

            return $this->render('ReservableActivityBundle:Admin:adminDetailsProperty.html.twig',
                array('details' => $details, 'pictures' => $arrayPictures));
        }
    }

    public function modifDeleteAction($property){
        if($property){
            if($this->getDoctrine()
                ->getRepository('ReservableActivityBundle:Activity')
                ->deleteProperty($property)){

                $this->getDoctrine()
                    ->getRepository('ReservableActivityBundle:Picture')
                    ->deleteAllByPropertyID($property);

                $allOwners = array();

                if($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')){

                    $properties = $this->getDoctrine()
                        ->getRepository('ReservableActivityBundle:Activity')
                        ->findAll();

                    $result = $this->getDoctrine()
                        ->getManager()
                        ->createQuery('SELECT u.email, u.id
										  FROM ReservableActivityBundle:Activity a
										  INNER JOIN UserUserBundle:Users u
										  WHERE u.id = a.ownerID
										  GROUP BY u.id')
                        ->getResult();

                    foreach($result as $oneResult){
                        $allOwners[$oneResult['id']]['email'] = $oneResult['email'];
                        $allOwners[$oneResult['id']]['ownerID'] = $oneResult['id'];
                    }
                }
                else{
                    $ownerID = $this->get('security.context')->getToken()->getUser()->getId();

                    $properties = $this->getDoctrine()
                        ->getRepository('ReservableActivityBundle:Activity')
                        ->findAllByOwnerID($ownerID);
                }

                $arrayPictures = array();
                if(!empty($properties)){
                    foreach($properties as $oneResult){
                        $firstImage = $this->getDoctrine()
                            ->getRepository('ReservableActivityBundle:Picture')
                            ->findAllByPropertyID($oneResult->getId());

                        if(!empty($firstImage[0]['path'])){
                            $arrayPictures[$oneResult->getId()] = $firstImage[0]['path'];
                        }
                    }
                }

                return $this->render('ReservableActivityBundle:View:viewActivities.html.twig',
                    array('properties' => $properties, 'pictures' => $arrayPictures, 'allOwners' => $allOwners));

            }
        }
    }

    public function getComments($propertyID){

        $resultQuery = $this->getDoctrine()
            ->getManager()
            ->createQuery("SELECT r.comentarios
                           FROM RagingsRatingBundle:Rating r INNER JOIN BookingsBookingBundle:Booking b
                           WHERE r.reservationNumber = b.id
                           AND b.activityID = '" . $propertyID . "'
                           AND r.comentarios != ''")
            ->getResult();

        return $resultQuery;
    }

    public function getRatings($propertyID){

        $resultQuery = $this->getDoctrine()
            ->getManager()
            ->createQuery("SELECT r.ubicacion, r.llegar, r.limpieza, r.material, r.caracteristicas, r.gestiones, r.usabilidad
                           FROM RagingsRatingBundle:Rating r INNER JOIN BookingsBookingBundle:Booking b
                           WHERE r.reservationNumber = b.id
                           AND b.activityID = '" . $propertyID . "'
                           AND r.comentarios != ''")
            ->getResult();

        $mean   = array();
        $mean['ubicacion']          = 0;
        $mean['llegar']             = 0;
        $mean['limpieza']           = 0;
        $mean['material']           = 0;
        $mean['caracteristicas']    = 0;
        $mean['gestiones']          = 0;
        $mean['usabilidad']         = 0;
        $total  = 0;
        $cont   = 0;
        if(!empty($resultQuery)){
            foreach($resultQuery as $oneResult){
                $mean['ubicacion']          += $oneResult['ubicacion'];         $total += $oneResult['ubicacion'];
                $mean['llegar']             += $oneResult['llegar'];            $total += $oneResult['llegar'];
                $mean['limpieza']           += $oneResult['limpieza'];          $total += $oneResult['limpieza'];
                $mean['material']           += $oneResult['material'];          $total += $oneResult['material'];
                $mean['caracteristicas']    += $oneResult['caracteristicas'];   $total += $oneResult['caracteristicas'];
                $mean['gestiones']          += $oneResult['gestiones'];         $total += $oneResult['gestiones'];
                $mean['usabilidad']         += $oneResult['usabilidad'];        $total += $oneResult['usabilidad'];

                $cont++;
            }

            $mean['ubicacion']          = $mean['ubicacion'] / $cont;
            $mean['llegar']             = $mean['llegar'] / $cont;
            $mean['limpieza']           = $mean['limpieza'] / $cont;
            $mean['material']           = $mean['material'] / $cont;
            $mean['caracteristicas']    = $mean['caracteristicas'] / $cont;
            $mean['gestiones']          = $mean['gestiones'] / $cont;
            $mean['usabilidad']         = $mean['usabilidad'] / $cont;

            $total = $total / ($cont * count($mean));
        }

        return array('ratings' => $mean, 'totalScore' => $total);
    }

    private function getDataLodging($property, Request $request){
        $session = $request->getSession();

        $details = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:Activity')
            ->findByPropertyID($property);

        // Mapa
        $lat = $details->getLat();$lng = $details->getLng();

        $marker = $this->get('ivory_google_map.marker');
        $marker->setPosition($lat, $lng);
        //ldd($marker);

        $map = $this->get('ivory_google_map.map');
        $map->setCenter($lat, $lng);
        $map->addMarker($marker);
        $map->setMapOptions(array('zoom' => 13));
        $map->setStylesheetOptions(array('width' => '100%'));
        //ldd($map);

        // Imagenes
        $pictures = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:Picture')
            ->findAllByPropertyID($property);

        $arrayPictures = array();
        if(!empty($pictures)) {
            foreach ($pictures as $onePicture) {
                $arrayPictures[] = $onePicture['path'];
            }
        }
        else{
            $arrayPictures[] = 'no-photo.jpg';
        }

        // tipos
        $types = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:TypeActivity')
            ->getAllTypes($details->getTypeRent());

        $typeSelected = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:ActivityyToType')
            ->getTypeSelected($property);

        $type = '';
        if($typeSelected){
            foreach($types as $key => $oneType){
                if($oneType['id'] == $typeSelected){
                    $type = $oneType['name'];
                }
            }
        }

        // features
        $features = array();
        if($typeSelected) {
            $features = $this->getAllFeaturesByType($typeSelected);

            $featuresSelected = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:ActivityToFeature')
                ->getAllFeatures($details->getId());

            if($featuresSelected){
                foreach($features as $key => $oneFeature){
                    if(in_array($oneFeature['id'], $featuresSelected)){
                        $features[$key]['selected'] = 1;
                    }
                }
            }
        }

        // Precios y temporadas
        $seasons = $this->getAllSeasonsByPropertyId($property);

        // Comentarios y valoraciones
        $comments       = $this->getComments($property);
        $resultRatings  = $this->getRatings($property);
        $ratings        = $resultRatings['ratings'];
        $totalRating    = $resultRatings['totalScore'];

        // Chart
        $categories = array('Ubicación', 'Cómo llegar', 'Limpieza', 'Material', 'Características', 'Gestiones', 'Usabilidad');
        $data = array(
            array('Ubicación',                                      $ratings['ubicacion']),
            array('Cómo llegar',                                    $ratings['llegar']),
            array('Limpieza',                                       $ratings['limpieza']),
            array('Material proporcionado',                         $ratings['material']),
            array('Características coindicen con las anunciadas',   $ratings['caracteristicas']),
            array('Gestiones',                                      $ratings['gestiones']),
            array('Usabilidad',                                     $ratings['usabilidad']),
        );

        $ob = new Highchart();
        $ob->chart->renderTo('ratingChart');
        $ob->chart->type('column');
        $ob->title->text('Resultados de la encuesta sobre 5 puntos');
        $ob->plotOptions->pie(array(
            'allowPointSelect'  => true,
            'cursor'            => 'pointer',
            'dataLabels'        => array('enabled' => false),
            'showInLegend'      => true
        ));
        $ob->xAxis->categories($categories);
        $ob->series(array(array('type' => 'column','name' => 'Valoración media', 'data' => $data)));

        // Preparamos resultados para devolver
        $result = array();
        $result['details']          = $details;
        $result['arrayPictures']    = $arrayPictures;
        $result['type']             = $type;
        $result['features']         = $features;
        $result['comments']         = $comments;
        $result['ratings']          = $ratings;
        $result['totalRating']      = $totalRating;
        $result['ob']               = $ob;
        $result['seasons']          = $seasons;
        $result['map']              = $map;

        return $result;
    }

    private function FormatDate($dateString){

        list($day, $month, $year) = explode('/', $dateString);
        return $year . $month . $day;
    }

    public function deleteFeatureAction(){

        if(isset($_POST['typeID']) && isset($_POST['featureID'])) {
            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("DELETE FROM ReservableActivityBundle:TypeToFeature ttf
                               WHERE ttf.typeID = " . $_POST['typeID'] . " AND ttf.featureID = " . $_POST['featureID'])
                ->getResult();

            return new JsonResponse(array('idDelete'=> "typeFeature-" .$_POST['typeID'] . "-" . $_POST['featureID']));
        }
        else return new JsonResponse(array());
    }

    public function addFeatureAction(){

        /*$_POST['typeID'] = 2;
        $_POST['featureID'] = 6;
        $_POST['featureName'] = 'nombre feature';
        $_POST['typeName'] = 'nombre tipo';*/

        if(isset($_POST['typeID']) && isset($_POST['featureID'])) {
            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("SELECT ttf.typeID, ttf.featureID FROM ReservableActivityBundle:TypeToFeature ttf
                               WHERE ttf.typeID = " . $_POST['typeID'] . " AND ttf.featureID = " . $_POST['featureID'])
                ->getResult();

            if(empty($resultQuery)){
                $typeToFeature = new TypeToFeature();
                $typeToFeature->setTypeID($_POST['typeID']);
                $typeToFeature->setFeatureID($_POST['featureID']);

                $em = $this->getDoctrine()->getManager();
                $em->persist($typeToFeature);
                //$em->flush();

                /*$response = "<tr id='typeFeature-" .$_POST['typeID']. "-" .$_POST['featureID']. "' class='oneType type-" .$_POST['typeID']. "' style='display: table-row;'>
                                    <td></td>
                                    <td>" .$_POST['featureName']. "</td>
                                    <td>" .$_POST['typeName']. "</td>
                                    <td typename='Tenis' typeid='" .$_POST['typeID']. "' featurename='Pista iluminada' featureid='" .$_POST['featureID']. "' style='color:red;' class='text-center deleteFeature'><i class='fa fa-minus-circle'></i></td>
                                </tr>";*/

                return new JsonResponse(array('typeID'=> $_POST['typeID'], 'featureID' => $_POST['featureID'], 'featureName' => $_POST['featureName'], 'typeName' => $_POST['typeName']));
            }
        else return new JsonResponse(array());

        }
        else return new JsonResponse(array());
    }

}