<?php

namespace Reservable\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Picture;
use Reservable\ActivityBundle\Form\Type\PictureType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureController extends Controller
{

	public function uploadFileAction()
	{
        // crea una task y le asigna algunos datos ficticios para este ejemplo
        $picture= new Picture();
        $picture->setActivityID(16);

        $form = $this->createForm(new PictureType(), $picture);

        return $this->render('ReservableActivityBundle:Picture:upload_picture_form.html.twig', 
        	array('form' => $form->createView(),
        ));
	}

    public function registerPictureAction()
    {
        $dm = $this->getDoctrine()->getManager();
        $form = $this->createForm(new PictureType());
        $form->bind($this->getRequest());

        if ($form->isValid()) {
			$picture = $form->getData();
			$picture->upload();
			$dm->persist($picture);
			$dm->flush();

			//return $this->redirect('picture-uploaded');
			return $this->redirect('view-properties');
        }

        return $this->render('ReservableActivityBundle:Picture:upload_picture_form.html.twig',
			array('form'=>$form->CreateView()));
    }

    public function loadImagesAction()
    {
        $request    = $this->getRequest();

        $images 	= $request->request->get('images');
        $activityID = $request->request->get('activityID');

        $lastPicture = (int)$this->getDoctrine()
            ->getRepository('ReservableActivityBundle:Picture')
            ->findLastIDimage($activityID);

        $arrayImagesNames = array();

        foreach($images as $image) {
            $lastPicture = $lastPicture + 1;
            $imageID     = $lastPicture;

            //$path = explode('/', $image);
            list($trash, $extension) = explode('.', $image['name']);
            $nameFile = $activityID . '_' . $imageID . '.' . $extension;

            $absRoute = Picture::DIRECTORYIMAGES . '/' . $nameFile;

            $content = file_get_contents($image['link']);
            $fp = file_put_contents($absRoute, $content);

            //$file = new UploadedFile($absRoute, $nameFile);
            $picture = new Picture();
            //$picture->setFile($file);
            $picture->setPath($nameFile);
            $picture->setActivityID($activityID);
            //$picture->upload($nameFile);

            $arrayImagesNames[] = $nameFile;

            $em = $this->getDoctrine()->getManager();
            $em->persist($picture);
        }

        $em->flush();

        return new JsonResponse(array('response'=>$image, 'id'=>$activityID, 'imagesNames' => $arrayImagesNames));
    }

}
