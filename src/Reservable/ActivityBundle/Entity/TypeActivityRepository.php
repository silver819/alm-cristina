<?php

namespace Reservable\ActivityBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * TypeActivityRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TypeActivityRepository extends EntityRepository
{
    public function getAllTypes($mode){

        $types = $this->getEntityManager()
            ->createQuery("SELECT t.name, t.id
                		   FROM ReservableActivityBundle:TypeActivity t
                		   WHERE t.mode LIKE '" . $mode . "'")
            ->getResult();


        return $types;
    }
}
