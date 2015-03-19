<?php

namespace User\UserBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Reservable\ActivityBundle\Entity\Activity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use User\UserBundle\Entity\Users;

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

	public function checkUserAction(){
		$request = $this->getRequest();

		$email 	   = $request->request->get('email');
		$google_id = $request->request->get('id');
		$result	   = false;

		$user = $this->getDoctrine()
					 ->getRepository('UserUserBundle:Users')
					 ->findByEmail($email);

		if(!empty($user)){
			$encoder_service 	= $this->get('security.encoder_factory');
			$encoder 			= $encoder_service->getEncoder($user);
			$encoded_pass 		= $encoder->encodePassword($google_id, $user->getSalt());

			if($encoded_pass == $user->getPassword()){
				$result = true;
			}
			else{
				$resultUpdatePassword = $this->getDoctrine()
                                			->getManager()
                                			->createQuery('UPDATE UserUserBundle:Users u
                                                		   SET u.password = \'' . $encoded_pass . '\'
                                                		   WHERE u.email = \'' . $email . '\'')
                                			->getResult();

				$result = $resultUpdatePassword;
			}
		}
		else{
			// Guardamos el usuario y logueamos
			$user = new Users();

			$salt 			 = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
			$encoder_service = $this->get('security.encoder_factory');
			$encoder 		 = $encoder_service->getEncoder($user);

			$username = substr($email, 0, strpos($email, '@'));

			$user->setName($request->request->get('given_name'));
			$user->setSurname($request->request->get('family_name'));
			$user->setUsername($username);
			$user->setUsernameCanonical($username);
			$user->setEmail($email);
			$user->setEmailCanonical($email);
			$user->setSalt($salt);
			$user->setPassword($encoder->encodePassword($google_id, $salt));
			$user->setRole('a:1:{i:0;s:9:"ROLE_USER";}');		
			$user->setEnabled(1);

			// extra
			$gplink 			= $request->request->get('link');
			$picture 		= $request->request->get('picture');

			$em = $this->getDoctrine()->getManager();
		    $em->persist($user);
		    $em->flush();

			$result = true;
		}

		return new JsonResponse(array('response'=>$result));
	}
}