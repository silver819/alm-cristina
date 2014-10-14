<?php

namespace Reservable\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Picture;
use Reservable\ActivityBundle\Form\Type\PictureType;

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

}
