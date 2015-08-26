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
    public function getAllTypes($mode = false){

        if($mode) {
            $types = $this->getEntityManager()
                ->createQuery("SELECT t.name, t.id, t.icon
                		   FROM ReservableActivityBundle:TypeActivity t
                		   WHERE t.mode LIKE '" . $mode . "'")
                ->getResult();
        }
        else{
            $types = $this->getEntityManager()
                ->createQuery("SELECT t.name, t.id, t.mode, t.icon
                		   FROM ReservableActivityBundle:TypeActivity t")
                ->getResult();
        }

        return $types;
    }
}
