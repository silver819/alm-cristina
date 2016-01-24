<?php

namespace Reservable\ActivityBundle\Controller;

use Reservable\ActivityBundle\Entity\TypeActivity;
use Reservable\ActivityBundle\Entity\TypeToFeature;
use Reservable\ActivityBundle\Entity\Features;
use Reservable\ActivityBundle\Entity\Picture;
use Reservable\ActivityBundle\Entity\ActivityyToType;
use Reservable\ActivityBundle\Entity\ActivityToFeature;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Seasons;
use Symfony\Component\HttpFoundation\Request;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminController extends Controller
{
    public function viewDetailsAction($property, Request $request)
    {

        $result = $this->getDataLodging($property, $request);

        // Propietarios
        $arrayOwners = $this->getAllUsers();

        return $this->render('ReservableActivityBundle:Admin:adminDetailsProperty.html.twig',
            array(
                'details' => $result['details'],
                'pictures' => $result['arrayPictures'],
                'type' => $result['type'],
                'features' => $result['features'],
                'comments' => $result['comments'],
                'ratings' => $result['ratings'],
                'totalRating' => $result['totalRating'],
                'chart' => $result['ob'],
                'seasons' => $result['seasons'],
                'map' => $result['map'],
                'cityName' => $result['cityName'],
                'arrayOwners' => $arrayOwners
            )
        );
    }

    public function modifDetailsAction($property, Request $request)
    {
        $session = $request->getSession();

        $details = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:Activity')
            ->findByPropertyID($property);

        // Mapa
        $zone       = $this->getDoctrine()->getRepository('ReservableActivityBundle:Zone')->findOneBy(array('id' => $details->getZone()));
        $zoneName   = $zone->getName();
        $lat        = $details->getLat();
        $lng        = $details->getLng();
        $zoom = 13;
        $marker = 1;

        if($lat == null && $lng == null){
            $lat = 40.4381307;
            $lng = -3.8199653;
            $zoom = 6;
            $marker = 0;
        }

        /*$map = $this->get('ivory_google_map.map');

        if(!$lat && ! $lng){
            $lat = '37.3164332';
            $lng = '-6.8257187';

            $zoom = 6;
        }
        else{
            $marker = $this->get('ivory_google_map.marker');
            $marker->setPosition($lat, $lng);
            //ldd($marker);

            $map->addMarker($marker);
            $zoom = 13;
        }

        $map->setCenter($lat, $lng);
        $map->setMapOptions(array('zoom' => $zoom));
        $map->setStylesheetOptions(array('width' => '100%'));*/
        //ldd($map);

        // Imagenes
        $pictures = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:Picture')
            ->findAllByPropertyID($property);

        $arrayPictures = array();
        foreach ($pictures as $onePicture) {
            $arrayPictures[] = $onePicture['path'];
        }

        // tipos
        $types = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:TypeActivity')
            ->getAllTypes($details->getTypeRent());

        $typeSelected = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:ActivityyToType')
            ->getTypeSelected($property);

        if ($typeSelected) {
            foreach ($types as $key => $oneType) {
                if ($oneType['id'] == $typeSelected) {
                    $types[$key]['selected'] = 1;
                }
            }
        }

        // features
        $features = array();
        if ($typeSelected) {
            $features = $this->getAllFeaturesByType($typeSelected);

            $featuresSelected = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:ActivityToFeature')
                ->getAllFeatures($details->getId());

            if ($featuresSelected) {
                foreach ($features as $key => $oneFeature) {
                    if (in_array($oneFeature['id'], $featuresSelected)) {
                        $features[$key]['selected'] = 1;
                    }
                }
            }
        }

        // Precios y temporadas
        $seasons = $this->getAllSeasonsByPropertyId($property);

        // Propietarios
        $arrayOwners = $this->getAllUsers();

        return $this->render('ReservableActivityBundle:Admin:modifDetailsProperty.html.twig',
            array('details' => $details,
                'lat' => $lat,
                'lng' => $lng,
                'zoom' => $zoom,
                'marker' => $marker,
                //'map' => $map,
                'zoneName' => $zoneName,
                'pictures' => $arrayPictures,
                'types' => $types,
                'features' => $features,
                'seasons' => $seasons,
                'arrayOwners' => $arrayOwners));
    }

    private function getAllUsers(){
        $users = $this->getDoctrine()
            ->getManager()
            ->createQuery("SELECT u.id, u.email
                           FROM UserUserBundle:Users u
                           WHERE u.enabled = 1 ORDER BY u.email ASC")
            ->getResult();

        return $users;
    }

    private function getAllFeaturesByType($type)
    {
        $features = $this->getDoctrine()
            ->getManager()
            ->createQuery("SELECT f.id, f.name
                           FROM ReservableActivityBundle:TypeToFeature ttf
                           INNER JOIN ReservableActivityBundle:Features f
                           WHERE ttf.featureID = f.id AND ttf.typeID = " . $type)
            ->getResult();

        return $features;
    }

    private function getAllSeasonsByPropertyId($propertyID)
    {

        $today = date('Ymd');

        $activity = $this->getDoctrine()->getRepository('ReservableActivityBundle:Activity')->findOneBy(array('id' => $propertyID));

        if($activity->getTypeRent() == 'day') {
            $seasons = $this->getDoctrine()
                ->getManager()
                ->createQuery("SELECT s.startSeason, s.endSeason, s.price
                           FROM ReservableActivityBundle:Seasons s
                           WHERE s.activityID =  " . $propertyID . "
                           AND s.endSeason > " . $today)
                ->getResult();

            if (!empty($seasons)) {
                foreach ($seasons as $key => $season) {
                    $seasons[$key]['startSeason'] = substr($season['startSeason'], 6, 2) . '/' . substr($season['startSeason'], 4, 2) . '/' . substr($season['startSeason'], 0, 4);
                    $seasons[$key]['endSeason'] = substr($season['endSeason'], 6, 2) . '/' . substr($season['endSeason'], 4, 2) . '/' . substr($season['endSeason'], 0, 4);
                }
            }
        }
        else{
            $seasons = $this->getDoctrine()
                ->getManager()
                ->createQuery("SELECT s.startSeason, s.endSeason, s.price
                           FROM ReservableActivityBundle:Seasons s
                           WHERE s.activityID =  " . $propertyID)
                ->getResult();
        }

        return $seasons;
    }

    public function saveModifDetailsAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        if ($_POST['productID']) {
            // Información general
            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("UPDATE ReservableActivityBundle:Activity a
                               SET   a.description = '" . $_POST['description'] . "',
                               a.address = '" . $_POST['address'] . "',
                               a.lat = '" . $_POST['latAddress'] . "',
                               a.lng = '" . $_POST['lngAddress'] . "',
                               a.ownerID = '" . $_POST['ownerSelect'] . "'
                               WHERE a.id = '" . $_POST['productID'] . "'")
                ->getResult();

            // Comprobación de que el propietario indicado realmente tiene el rol correcto

            // Temporadas
            if( isset($_POST['Seasons']) && !empty($_POST['Seasons'])) {

                // Borramos las temporadas anteriores
                $resultQuery = $this->getDoctrine()->getManager()
                    ->createQuery("DELETE FROM  ReservableActivityBundle:Seasons s WHERE s.activityID = " . $_POST['productID'])
                    ->getResult();

                foreach ($_POST['Seasons'] as $oneSeason) {
                    if ($oneSeason['From'] != '' && $oneSeason['To'] != '' && $oneSeason['Price'] != '') {
                        $season = new Seasons();
                        $season->setActivityID($_POST['productID']);
                        if ($_POST['typeRent'] == 'hour') {
                            $season->setStartSeason($oneSeason['From']);
                            $season->setEndSeason($oneSeason['To']);
                        } else {
                            $season->setStartSeason($this->FormatDate($oneSeason['From']));
                            $season->setEndSeason($this->FormatDate($oneSeason['To']));
                        }
                        $season->setPrice($oneSeason['Price']);

                        $em->persist($season);
                        $em->flush();
                    }
                }
            }

            // Tipos y features
            if (isset($_POST['type'])) {
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
            if (isset($_POST['feature']) && !empty($_POST['feature'])) {
                foreach ($_POST['feature'] as $oneFeature) {
                    $activityFeatureEquivalence = new ActivityToFeature();
                    $activityFeatureEquivalence->setActivityID($_POST['productID']);
                    $activityFeatureEquivalence->setFeatureID($oneFeature);

                    $em->persist($activityFeatureEquivalence);
                }
            }

            // Eliminar imagenes
            if(isset($_POST['deletePicture']) && !empty($_POST['deletePicture'])){
                foreach($_POST['deletePicture'] as $picture => $state){
                    unlink(Picture::DIRECTORYIMAGES . '/' . $picture);

                    $pictureObject = $this->getDoctrine()->getRepository('ReservableActivityBundle:Picture')->findOneBy(array('path' => $picture));
                    $em->remove($pictureObject);
                }
            }

            $em->flush();

            return $this->redirect($this->generateUrl('adminDetails', array('property' => $_POST['productID'])));
        }
    }

    public function modifActiveAction($property)
    {
        if ($property) {
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
            foreach ($pictures as $onePicture) {
                $arrayPictures[] = $onePicture['path'];
            }

            return $this->render('ReservableActivityBundle:Admin:adminDetailsProperty.html.twig',
                array('details' => $details, 'pictures' => $arrayPictures));
        }
    }

    public function modifDeactiveAction($property)
    {
        if ($property) {
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
            foreach ($pictures as $onePicture) {
                $arrayPictures[] = $onePicture['path'];
            }

            return $this->render('ReservableActivityBundle:Admin:adminDetailsProperty.html.twig',
                array('details' => $details, 'pictures' => $arrayPictures));
        }
    }

    public function modifDeleteAction($property)
    {
        if ($property) {
            if ($this->getDoctrine()
                ->getRepository('ReservableActivityBundle:Activity')
                ->deleteProperty($property)
            ) {

                $this->getDoctrine()
                    ->getRepository('ReservableActivityBundle:Picture')
                    ->deleteAllByPropertyID($property);

                $allOwners = array();

                if ($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {

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

                    foreach ($result as $oneResult) {
                        $allOwners[$oneResult['id']]['email'] = $oneResult['email'];
                        $allOwners[$oneResult['id']]['ownerID'] = $oneResult['id'];
                    }
                } else {
                    $ownerID = $this->get('security.context')->getToken()->getUser()->getId();

                    $properties = $this->getDoctrine()
                        ->getRepository('ReservableActivityBundle:Activity')
                        ->findAllByOwnerID($ownerID);
                }

                $arrayPictures = array();
                if (!empty($properties)) {
                    foreach ($properties as $oneResult) {
                        $firstImage = $this->getDoctrine()
                            ->getRepository('ReservableActivityBundle:Picture')
                            ->findAllByPropertyID($oneResult->getId());

                        if (!empty($firstImage[0]['path'])) {
                            $arrayPictures[$oneResult->getId()] = $firstImage[0]['path'];
                        }
                    }
                }

                $cities = $this->getDoctrine()->getRepository("ReservableActivityBundle:Zone")->findBy(array("type" => 5));
                $cityNames = array();
                foreach($cities as $city){
                    $cityNames[$city->getId()]['name'] = $city->getName();
                    $cityNames[$city->getId()]['id'] = $city->getId();
                }

                return $this->render('ReservableActivityBundle:View:viewActivities.html.twig',
                    array('properties' => $properties, 'pictures' => $arrayPictures, 'allOwners' => $allOwners, 'cityNames' => $cityNames));

            }
        }
    }

    public function getComments($propertyID)
    {

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

    public function getRatings($propertyID)
    {

        $resultQuery = $this->getDoctrine()
            ->getManager()
            ->createQuery("SELECT r.ubicacion, r.llegar, r.limpieza, r.material, r.caracteristicas, r.gestiones, r.usabilidad
                           FROM RagingsRatingBundle:Rating r INNER JOIN BookingsBookingBundle:Booking b
                           WHERE r.reservationNumber = b.id
                           AND b.activityID = '" . $propertyID . "'
                           AND r.comentarios != ''")
            ->getResult();

        $mean = array();
        $mean['ubicacion'] = 0;
        $mean['llegar'] = 0;
        $mean['limpieza'] = 0;
        $mean['material'] = 0;
        $mean['caracteristicas'] = 0;
        $mean['gestiones'] = 0;
        $mean['usabilidad'] = 0;
        $total = 0;
        $cont = 0;
        if (!empty($resultQuery)) {
            foreach ($resultQuery as $oneResult) {
                $mean['ubicacion'] += $oneResult['ubicacion'];
                $total += $oneResult['ubicacion'];
                $mean['llegar'] += $oneResult['llegar'];
                $total += $oneResult['llegar'];
                $mean['limpieza'] += $oneResult['limpieza'];
                $total += $oneResult['limpieza'];
                $mean['material'] += $oneResult['material'];
                $total += $oneResult['material'];
                $mean['caracteristicas'] += $oneResult['caracteristicas'];
                $total += $oneResult['caracteristicas'];
                $mean['gestiones'] += $oneResult['gestiones'];
                $total += $oneResult['gestiones'];
                $mean['usabilidad'] += $oneResult['usabilidad'];
                $total += $oneResult['usabilidad'];

                $cont++;
            }

            $mean['ubicacion'] = round($mean['ubicacion'] / $cont, 2);
            $mean['llegar'] = round($mean['llegar'] / $cont, 2);
            $mean['limpieza'] = round($mean['limpieza'] / $cont, 2);
            $mean['material'] = round($mean['material'] / $cont, 2);
            $mean['caracteristicas'] = round($mean['caracteristicas'] / $cont, 2);
            $mean['gestiones'] = round($mean['gestiones'] / $cont, 2);
            $mean['usabilidad'] = round($mean['usabilidad'] / $cont, 2);

            $total = $total / ($cont * count($mean));
        }

        return array('ratings' => $mean, 'totalScore' => round($total, 2));
    }

    private function getDataLodging($property, Request $request)
    {
        $session = $request->getSession();

        $details = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:Activity')
            ->findByPropertyID($property);

        // Mapa
        $map = $this->get('ivory_google_map.map');
        $map->setStylesheetOptions(array('width' => '100%'));
        $city = $this->getDoctrine()->getRepository('ReservableActivityBundle:Zone')->findOneBy(array('id' => $details->getZone()));
        $cityName = $city->getName();

        $lat = $details->getLat();
        $lng = $details->getLng();
        if($lat && $lng){
            $marker = $this->get('ivory_google_map.marker');
            $marker->setPosition($lat, $lng);
            //ldd($marker);

            $map->setMapOptions(array('zoom' => 13));
            $map->setCenter($lat, $lng);
            $map->addMarker($marker);
            //ldd($map);
        }
        else{
            $map->setCenter(37.333351, -4.5765007);
            $map->setMapOptions(array('zoom' => 7));
        }

        // Imagenes
        $pictures = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:Picture')
            ->findAllByPropertyID($property);

        $arrayPictures = array();
        if (!empty($pictures)) {
            foreach ($pictures as $onePicture) {
                $arrayPictures[] = $onePicture['path'];
            }
        } else {
            $arrayPictures[] = 'no-photo.jpg';
        }

        // Tipos
        $types = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:TypeActivity')
            ->getAllTypes($details->getTypeRent());

        $typeSelected = $this->getDoctrine()
            ->getRepository('ReservableActivityBundle:ActivityyToType')
            ->getTypeSelected($property);

        $type = '';
        if ($typeSelected) {
            foreach ($types as $key => $oneType) {
                if ($oneType['id'] == $typeSelected) {
                    $type = $oneType['name'];
                }
            }
        }

        // Features
        $features = array();
        if ($typeSelected) {
            $features = $this->getAllFeaturesByType($typeSelected);

            $featuresSelected = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:ActivityToFeature')
                ->getAllFeatures($details->getId());

            if ($featuresSelected) {
                foreach ($features as $key => $oneFeature) {
                    if (in_array($oneFeature['id'], $featuresSelected)) {
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
            array('Ubicación', $ratings['ubicacion']),
            array('Cómo llegar', $ratings['llegar']),
            array('Limpieza', $ratings['limpieza']),
            array('Material proporcionado', $ratings['material']),
            array('Características coindicen con las anunciadas', $ratings['caracteristicas']),
            array('Gestiones', $ratings['gestiones']),
            array('Usabilidad', $ratings['usabilidad']),
        );

        $ob = new Highchart();
        $ob->chart->renderTo('ratingChart');
        $ob->chart->type('column');
        $ob->title->text('Resultados de la encuesta sobre 5 puntos');
        $ob->plotOptions->pie(array(
            'allowPointSelect' => true,
            'cursor' => 'pointer',
            'dataLabels' => array('enabled' => false),
            'showInLegend' => true
        ));
        $ob->xAxis->categories($categories);
        $ob->series(array(array('type' => 'column', 'name' => 'Valoración media', 'data' => $data)));

        // Preparamos resultados para devolver
        $result = array();
        $result['details'] = $details;
        $result['arrayPictures'] = $arrayPictures;
        $result['type'] = $type;
        $result['features'] = $features;
        $result['comments'] = $comments;
        $result['ratings'] = $ratings;
        $result['totalRating'] = $totalRating;
        $result['ob'] = $ob;
        $result['seasons'] = $seasons;
        $result['map'] = $map;
        $result['cityName'] = $cityName;

        return $result;
    }

    private function FormatDate($dateString)
    {

        list($day, $month, $year) = explode('/', $dateString);
        return $year . $month . $day;
    }

    public function deleteFeatureAction()
    {

        if (isset($_POST['typeID']) && isset($_POST['featureID'])) {
            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("DELETE FROM ReservableActivityBundle:TypeToFeature ttf
                               WHERE ttf.typeID = " . $_POST['typeID'] . " AND ttf.featureID = " . $_POST['featureID'])
                ->getResult();

            return new JsonResponse(array('idDelete' => "typeFeature-" . $_POST['typeID'] . "-" . $_POST['featureID']));
        } else return new JsonResponse(array());
    }

    public function addFeatureAction()
    {

        /*$_POST['typeID'] = 2;
        $_POST['featureID'] = 6;
        $_POST['featureName'] = 'nombre feature';
        $_POST['typeName'] = 'nombre tipo';*/

        if (isset($_POST['typeID']) && isset($_POST['featureID'])) {
            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("SELECT ttf.typeID, ttf.featureID FROM ReservableActivityBundle:TypeToFeature ttf
                               WHERE ttf.typeID = " . $_POST['typeID'] . " AND ttf.featureID = " . $_POST['featureID'])
                ->getResult();

            if (empty($resultQuery)) {
                $typeToFeature = new TypeToFeature();
                $typeToFeature->setTypeID($_POST['typeID']);
                $typeToFeature->setFeatureID($_POST['featureID']);

                $em = $this->getDoctrine()->getManager();
                $em->persist($typeToFeature);
                $em->flush();

                return new JsonResponse(
                    array(
                        'typeID' => $_POST['typeID'],
                        'featureID' => $_POST['featureID'],
                        'featureName' => $_POST['featureName'],
                        'typeName' => $_POST['typeName']
                    )
                );
            } else return new JsonResponse(array());

        } else return new JsonResponse(array());
    }

    public function deleteTypeAction()
    {

        if (isset($_POST['typeID'])) {
            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("DELETE FROM ReservableActivityBundle:TypeToFeature ttf
                               WHERE ttf.typeID = " . $_POST['typeID'])
                ->getResult();

            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("DELETE FROM ReservableActivityBundle:ActivityyToType ttf
                               WHERE ttf.typeID = " . $_POST['typeID'])
                ->getResult();

            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("DELETE FROM ReservableActivityBundle:TypeActivity ttf
                               WHERE ttf.id = " . $_POST['typeID'])
                ->getResult();

            return new JsonResponse(array('idDelete' => $_POST['typeID']));
        } else return new JsonResponse(array());
    }

    public function modifyTypeAction()
    {

        if (isset($_POST['typeID']) && isset($_POST['typeIName']) && isset($_POST['typeModality'])) {

            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("UPDATE  ReservableActivityBundle:TypeActivity ta
                               SET   ta.name = '" . $_POST['typeIName'] . "',
                               ta.mode = '" . $_POST['typeModality'] . "',
                               ta.icon = '" . $_POST['typeIcon'] . "'
                               WHERE ta.id = '" . $_POST['typeID'] . "'")
                ->getResult();

            return new JsonResponse(array('typeID' => $_POST['typeID'], 'typeName' => $_POST['typeIName'], 'typeModality' => $_POST['typeModality'], 'typeIcon' => $_POST['typeIcon']));
        } else return new JsonResponse(array());
    }

    public function addTypeAction()
    {

        if (isset($_POST['typeName']) && isset($_POST['typeModality']) && isset($_POST['typeIcon']) && $_POST['typeName'] != '' && $_POST['typeModality'] != 'null') {

            $newType = new TypeActivity();
            $newType->setName($_POST['typeName']);
            $newType->setMode($_POST['typeModality']);
            $newType->setIcon($_POST['typeIcon']);
            $newType->setEs($_POST['typeName']);
            $newType->setEn($_POST['typeName']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newType);
            $em->flush();

            $response = array();
            $response['name'] = $_POST['typeName'];
            if($_POST['typeModality'] == 'day'){
                if($request = $this->get('request')->getLocale() == 'es') {
                    $response['modality'] = 'Por día';
                }
                else{
                    $response['modality'] = 'By day';
                }
            }
            else {
                if($request = $this->get('request')->getLocale() == 'es') {
                    $response['modality'] = 'Por hora';
                }
                else{
                    $response['modality'] = 'By hour';
                }
            }
            $response['icon'] = $_POST['typeIcon'];
            $response['id'] = $this->getDoctrine()->getRepository('ReservableActivityBundle:TypeActivity')->getIdByName($_POST['typeName']);

            return new JsonResponse($response);
        } else return new JsonResponse(array());
    }

    public function deleteAdminFeatureAction()
    {

        if (isset($_POST['typeID'])) {

            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("DELETE FROM ReservableActivityBundle:TypeToFeature ttf
                               WHERE ttf.featureID = " . $_POST['typeID'])
                ->getResult();

            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("DELETE FROM ReservableActivityBundle:ActivityToFeature ttf
                               WHERE ttf.featureID = " . $_POST['typeID'])
                ->getResult();

            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("DELETE FROM ReservableActivityBundle:Features ttf
                               WHERE ttf.id = " . $_POST['typeID'])
                ->getResult();

            return new JsonResponse(array('idDelete' => $_POST['typeID']));
        }
        else return new JsonResponse(array());
    }

    public function addAdminFeatureAction(){

        if (isset($_POST['typeName'])) {

            $newFeature = new Features();
            $newFeature->setName($_POST['typeName']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newFeature);
            $em->flush();

            $response = array();
            $response['name'] = $_POST['typeName'];
            $response['id'] = $this->getDoctrine()
                ->getRepository('ReservableActivityBundle:Features')
                ->getIdByName($_POST['typeName']);


            return new JsonResponse($response);
        }
        else return new JsonResponse(array());

    }

    public function modifAdminFeatureAction(){

        if (isset($_POST['typeName']) && isset($_POST['typeID'])) {

            $resultQuery = $this->getDoctrine()
                ->getManager()
                ->createQuery("UPDATE  ReservableActivityBundle:Features ta
                               SET   ta.name = '" . $_POST['typeName'] . "'
                               WHERE ta.id = '" . $_POST['typeID'] . "'")
                ->getResult();

            return new JsonResponse(array('typeID' => $_POST['typeID'], 'typeName' => $_POST['typeName']));
        }
        else return new JsonResponse(array());
    }
}