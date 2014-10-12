<?php

namespace Reservable\ActivityBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ActivityRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ActivityRepository extends EntityRepository
{
	function findAllByOwnerID($ownerID){
		
		return $this->getEntityManager()
            		->createQuery('SELECT p 
                				   FROM ReservableActivityBundle:Activity p
                				   WHERE p.ownerID = ' . $ownerID)
            		->getResult();
	}

	function activeProperty($propertyID){
		return$this->getEntityManager()
            	   ->createQuery('UPDATE ReservableActivityBundle:Activity p
            	   				  SET p.active = 1
                				  WHERE p.id = ' . $propertyID)
            		->getResult();
	}

	function deactiveProperty($propertyID){
		return$this->getEntityManager()
            	   ->createQuery('UPDATE ReservableActivityBundle:Activity p
            	   				  SET p.active = 0
                				  WHERE p.id = ' . $propertyID)
            		->getResult();
	}

	function deleteProperty($propertyID){
		return$this->getEntityManager()
            	   ->createQuery('DELETE FROM ReservableActivityBundle:Activity p
                				  WHERE p.id = ' . $propertyID)
            		->getResult();
	}

	function setValues($propertyID, $setValues){
		return$this->getEntityManager()
            	   ->createQuery('UPDATE ReservableActivityBundle:Activity p
            	   				  SET ' . $setValues . ' 
            	   				  WHERE p.id = ' . $propertyID)
            		->getResult();
	}
}
