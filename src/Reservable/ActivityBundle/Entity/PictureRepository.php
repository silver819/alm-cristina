<?php

namespace Reservable\ActivityBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PictureRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PictureRepository extends EntityRepository
{
	function findAllIn($picturesIDs){
		
		return $this->getEntityManager()
            		->createQuery('SELECT p 
                				   FROM ReservableActivityBundle:Picture p
                				   WHERE p.activityID IN (' . $picturesIDs . ')')
            		->getResult();
	}

	function deleteAllByPropertyID($propertyID){
        return $this->getEntityManager()
               	    ->createQuery('DELETE FROM ReservableActivityBundle:Picture p
                                   WHERE p.activityID = ' . $propertyID)
                    ->getResult();
	}

  function findAllByPropertyID($propertyID){
        return $this->getEntityManager()
                    ->createQuery('SELECT p.path
                                   FROM ReservableActivityBundle:Picture p
                                   WHERE p.activityID = ' . $propertyID)
                    ->getResult();
  }

    function findLastIDimage($propertyID){
        $id = 0;

        $result = $this->getEntityManager()
            ->createQuery("SELECT p.path
                           FROM ReservableActivityBundle:Picture p
                           WHERE p.path LIKE '" . $propertyID . "_%'
                           ORDER BY p.path DESC")
            ->getResult();

        if(!empty($result)){
            list($trash, $extension) = explode('_', $result[0]['path']);
            list($id, $extension) = explode('.', $extension);
        }

        return $id;
    }
  
}
