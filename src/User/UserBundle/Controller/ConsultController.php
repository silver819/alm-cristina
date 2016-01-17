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
            //ldd($oneUser);
			$aux['userID']		= ucwords($oneUser->getID());
			$aux['email']		= $oneUser->getEmail();
			$aux['name']		= ucwords($oneUser->getName());
			$aux['surname']		= ucwords($oneUser->getSurname());
			$aux['phone']		= $oneUser->getPhoneNumber();
			$aux['mobile']	    = $oneUser->getMobileNumber();
			$aux['lastLogin']	= $oneUser->getLastLogin();
			$aux['active']	    = $oneUser->isEnabled();
			$role				= $oneUser->getRole();
			$aux['role']		= $role[0];

            $aux['properties']  = array();
            switch($role[0]){
                case 'ROLE_USER':


                    break;
                case 'ROLE_ADMIN':
                    $aux['properties'] = $this->getUserProperties($oneUser->getID());

                    break;
                case 'ROLE_SUPER_ADMIN':


                    break;
            }

			$allUsers[] = $aux;

			$auxEmail['ownerID']	= $oneUser->getID();
			$auxEmail['email']		= $oneUser->getEmail();
			$allEmails[] 			= $auxEmail;
		}

		return $this->render('UserUserBundle:Consult:viewUsers.html.twig', 
			array('allUsers' => $allUsers, 'allEmails' => $allEmails));

	}

    public function viewUsersModifAction($userId){
        if(!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        $thisUser = $this->getDoctrine()
            ->getRepository('UserUserBundle:Users')
            ->getUserByUserID($userId);

        $thisUserData = array();
        $thisUserData['name']       = $thisUser->getName();
        $thisUserData['email']      = $thisUser->getEmail();
        $thisUserData['id']         = $thisUser->getId();
        $thisUserData['role']       = $thisUser->getRole();
        $thisUserData['surname']    = $thisUser->getSurname();
        $thisUserData['activeUser']     = ( $thisUser->isEnabled() ) ? 1 : 0 ;

        switch($thisUserData['role'][0]){
            case 'ROLE_USER':


                break;
            case 'ROLE_ADMIN':
                $thisUserData['properties'] = $this->getUserProperties($thisUserData['id']);

                break;
            case 'ROLE_SUPER_ADMIN':


                break;
        }

        //ldd($thisUserData);

        return $this->render('UserUserBundle:Consult:viewUsersModif.html.twig',
            array('oneUser' => $thisUserData));
    }

    public function deleteUserAction($userId){
        if(!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        // Eliminar usuario
        $adminId = $this->get('security.context')->getToken()->getUser()->getId();

        $changeOwner = $this->getDoctrine()
            ->getManager()
            ->createQuery("UPDATE ReservableActivityBundle:Activity a
                           SET   a.ownerID = " . $adminId . ", a.active = 0
                           WHERE a.ownerID = " . $userId)
            ->getResult();

        $deleteOwner = $this->getDoctrine()->getManager()
            ->createQuery("DELETE FROM UserUserBundle:Users u WHERE u.id = " . $userId)
            ->getResult();

        return $this->redirect($this->generateUrl('view_users', array(), 'prod'));
    }

    public function activeUserAction($userId){
        if(!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        // Activar usuario
        $activeOwner = $this->getDoctrine()->getManager()
            ->createQuery("UPDATE UserUserBundle:Users u SET u.enabled = 1 WHERE u.id = " . $userId)
            ->getResult();

        $request = $this->getRequest();
        $env = ($this->get('kernel')->getEnvironment() == 'dev')? 'app_dev.php' : 'app.php' ;
        $url = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/' . $env . '/' . $request->getLocale() . '/view-users';

        return $this->redirect($url);
    }

    public function deactiveUserAction($userId){
        if(!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        // Activar usuario
        $deactiveOwner = $this->getDoctrine()->getManager()
            ->createQuery("UPDATE UserUserBundle:Users u SET u.enabled = 0 WHERE u.id = " . $userId)
            ->getResult();

        $request = $this->getRequest();
        $env = ($this->get('kernel')->getEnvironment() == 'dev')? 'app_dev.php' : 'app.php' ;
        $url = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/' . $env . '/' . $request->getLocale() . '/view-users';

        return $this->redirect($url);
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

    public function getUserProperties($userID){
        $properties = $this->getDoctrine()
                      ->getManager()
                      ->createQuery('SELECT a
                                     FROM ReservableActivityBundle:Activity a
                                     WHERE a.ownerID = ' . $userID)
                      ->getResult();

        $result = array();
        if(!empty($properties)){
            foreach($properties as $oneProperty){
                $aux['id'] = $oneProperty->getId();
                $aux['name'] = $oneProperty->getName();
                $aux['numBookings'] = $this->getNumBookings($oneProperty->getId());

                $result[] = $aux;
            }
        }

        return $result;
    }

    public function getNumBookings($activityID){
        $bookings = $this->getDoctrine()
            ->getManager()
            ->createQuery('SELECT count(b)
                           FROM BookingsBookingBundle:Booking b
                           WHERE b.activityID = ' . $activityID)
            ->getResult();

        $num = 0;
        if(!empty($bookings)){
            $num = $bookings[0][1];
        }

        return $num;
    }
}