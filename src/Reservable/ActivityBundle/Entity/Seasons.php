<?php

namespace Reservable\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Seasons
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Reservable\ActivityBundle\Entity\SeasonsRepository")
 */
class Seasons
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
     * @var \String
     *
     * @ORM\Column(name="startSeason", type="string")
     */
    private $startSeason;

    /**
     * @var \String
     *
     * @ORM\Column(name="endSeason", type="string")
     */
    private $endSeason;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;


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
     * @return Seasons
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
     * Set startSeason
     *
     * @param \DateTime $startSeason
     * @return Seasons
     */
    public function setStartSeason($startSeason)
    {
        $this->startSeason = $startSeason;
    
        return $this;
    }

    /**
     * Get startSeason
     *
     * @return \DateTime 
     */
    public function getStartSeason()
    {
        return $this->startSeason;
    }

    /**
     * Set endSeason
     *
     * @param \DateTime $endSeason
     * @return Seasons
     */
    public function setEndSeason($endSeason)
    {
        $this->endSeason = $endSeason;
    
        return $this;
    }

    /**
     * Get endSeason
     *
     * @return \DateTime 
     */
    public function getEndSeason()
    {
        return $this->endSeason;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Seasons
     */
    public function setPrice($price)
    {
        $this->price = $price;
    
        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }
}
