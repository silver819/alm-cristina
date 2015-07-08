<?php

namespace Reservable\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityyToType
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Reservable\ActivityBundle\Entity\ActivityyToTypeRepository")
 */
class ActivityyToType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="activityID", type="integer")
     */
    private $activityID;

    /**
     * @var integer
     *
     * @ORM\Column(name="typeID", type="integer")
     */
    private $typeID;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set activityID
     *
     * @param integer $activityID
     * @return ActivityyToType
     */
    public function setActivityID($activityID)
    {
        $this->activityID = $activityID;
    
        return $this;
    }

    /**
     * Get activityID
     *
     * @return integer 
     */
    public function getActivityID()
    {
        return $this->activityID;
    }

    /**
     * Set typeID
     *
     * @param integer $typeID
     * @return ActivityyToType
     */
    public function setTypeID($typeID)
    {
        $this->typeID = $typeID;
    
        return $this;
    }

    /**
     * Get typeID
     *
     * @return integer 
     */
    public function getTypeID()
    {
        return $this->typeID;
    }
}
