<?php

namespace Reservable\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityToFeature
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Reservable\ActivityBundle\Entity\ActivityToFeatureRepository")
 */
class ActivityToFeature
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
     * @ORM\Column(name="featureID", type="integer")
     */
    private $featureID;


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
     * @return ActivityToFeature
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
     * Set featureID
     *
     * @param integer $featureID
     * @return ActivityToFeature
     */
    public function setFeatureID($featureID)
    {
        $this->featureID = $featureID;
    
        return $this;
    }

    /**
     * Get featureID
     *
     * @return integer 
     */
    public function getFeatureID()
    {
        return $this->featureID;
    }
}
