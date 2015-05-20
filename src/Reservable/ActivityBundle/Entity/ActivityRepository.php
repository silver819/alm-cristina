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

    function findAll(){
        return $this->getEntityManager()
                    ->createQuery('SELECT p 
                                   FROM ReservableActivityBundle:Activity p')
                    ->getResult();
    }

	function activeProperty($propertyID){
		return $this->getEntityManager()
            	    ->createQuery('UPDATE ReservableActivityBundle:Activity p
            	   				  SET p.active = 1
                				  WHERE p.id = ' . $propertyID)
            		->getResult();
	}

	function deactiveProperty($propertyID){
		return $this->getEntityManager()
            	   ->createQuery('UPDATE ReservableActivityBundle:Activity p
            	   				  SET p.active = 0
                				  WHERE p.id = ' . $propertyID)
            		->getResult();
	}

	function deleteProperty($propertyID){
		return $this->getEntityManager()
            	    ->createQuery('DELETE FROM ReservableActivityBundle:Activity p
                				  WHERE p.id = ' . $propertyID)
            		->getResult();
	}

	function setValues($propertyID, $setValues){
		return $this->getEntityManager()
            	    ->createQuery('UPDATE ReservableActivityBundle:Activity p
            	   				  SET ' . $setValues . ' 
            	   				  WHERE p.id = ' . $propertyID)
            		->getResult();
	}

    function getPropertiesWhere($where){
        return $this->getEntityManager()
                    ->createQuery('SELECT p
                                  FROM ReservableActivityBundle:Activity p
                                  WHERE ' . $where . ' AND p.active=1')
                    ->getResult();
    }

    function getLastActivityID(){
        return $this->getEntityManager()
                    ->createQuery('SELECT MAX(p.id)
                                  FROM ReservableActivityBundle:Activity p')
                    ->getResult();
    }

    function findByPropertyID($propertyID){
        $property = new Activity();

        $result = $this->getEntityManager()
                    ->createQuery('SELECT p
                                  FROM ReservableActivityBundle:Activity p
                                  WHERE p.id = ' . $propertyID)
                    ->getResult();

        if(isset($result[0])) $property = $result[0];

        return $property;
    }

    function findNameByActivityID($propertyID){
        $result = $this->getEntityManager()
                    ->createQuery('SELECT p.name
                                  FROM ReservableActivityBundle:Activity p
                                  WHERE p.id = ' . $propertyID)
                    ->getResult();

        return $result[0]['name'];
    }
    
}
