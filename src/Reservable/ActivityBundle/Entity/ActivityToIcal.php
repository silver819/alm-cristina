<?php

namespace Reservable\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityToIcal
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Reservable\ActivityBundle\Entity\ActivityToIcalRepository")
 */
class ActivityToIcal
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
     * @var string
     *
     * @ORM\Column(name="icalUrl", type="string", length=255)
     */
    private $icalUrl;


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
     * @return ActivityToIcal
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
     * Set icalUrl
     *
     * @param string $icalUrl
     * @return ActivityToIcal
     */
    public function setIcalUrl($icalUrl)
    {
        $this->icalUrl = $icalUrl;
    
        return $this;
    }

    /**
     * Get icalUrl
     *
     * @return string 
     */
    public function getIcalUrl()
    {
        return $this->icalUrl;
    }
}
