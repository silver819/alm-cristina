<?php

namespace User\UserBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Activity;
use Symfony\Component\HttpFoundation\Request;

class ConsultController extends Controller
{
	public function viewUsersAction()
	{
		if(!$this->get('security.context')->isGranted('ROLE_USER')) {
			throw new AccessDeniedException();
		}

		$allUsers = array();
		
		$users = $this->getDoctrine()
						   ->getRepository('UserUserBundle:Users')
						   ->findAllUsers();

		foreach($users as $oneUser){
			$aux['userID']		= ucwords($oneUser->getID());
			$aux['name']		= ucwords($oneUser->getName());
			$aux['surname']		= ucwords($oneUser->getSurname());
			$role				= $oneUser->getRole();
			$aux['role']		= $role[0];

			$allUsers[] = $aux;

			$auxEmail['ownerID']	= $oneUser->getID();
			$auxEmail['email']		= $oneUser->getEmail();
			$allEmails[] 			= $auxEmail;
		}

		return $this->render('UserUserBundle:Consult:viewUsers.html.twig', 
			array('allUsers' => $allUsers, 'allEmails' => $allEmails));

	}
}