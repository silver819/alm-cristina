<?php

namespace Reservable\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Activity
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Reservable\ActivityBundle\Entity\ActivityRepository")
 */
class Activity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="zone", type="integer")
     */
    private $zone;

    /**
     * @var integer
     *
     * @ORM\Column(name="ownerID", type="integer")
     */
    private $ownerID;

    /**
     * @var string
     *
     * @ORM\Column(name="typeRent", type="string", length=5)
     */
    private $typeRent;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var float
     *
     * @ORM\Column(name="lat", type="float", nullable=true)
     */
    private $lat;

    /**
     * @var float
     *
     * @ORM\Column(name="lng", type="float", nullable=true)
     */
    private $lng;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="active", type="integer", nullable=true)
     */
    private $active;

    /**
     * @var pictures[]
     **/
    private $pictures;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     * @return Activity
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set zone
     *
     * @param int $zone
     * @return Activity
     */
    public function setZone($zone)
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * Get name
     *
     * @return int
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Set ownerID
     *
     * @param integer $ownerID
     * @return Activity
     */
    public function setOwnerID($ownerID)
    {
        $this->ownerID = $ownerID;
    
        return $this;
    }

    /**
     * Get ownerID
     *
     * @return integer 
     */
    public function getOwnerID()
    {
        return $this->ownerID;
    }

    /**
     * Set typeRent
     *
     * @param string $typeRent
     * @return Activity
     */
    public function setTypeRent($typeRent)
    {
        $this->typeRent = $typeRent;
    
        return $this;
    }

    /**
     * Get typeRent
     *
     * @return string 
     */
    public function getTypeRent()
    {
        return $this->typeRent;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Activity
     */
    public function setAddress($address)
    {
        $this->address = $address;
    
        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set lat
     *
     * @param float $lat
     * @return Activity
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    
        return $this;
    }

    /**
     * Get lat
     *
     * @return float 
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param float $lng
     * @return Activity
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    
        return $this;
    }

    /**
     * Get lng
     *
     * @return float 
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Activity
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set active
     *
     * @param integer $active
     * @return Activity
     */
    public function setActive($active)
    {
        $this->active = $active;
    
        return $this;
    }

    /**
     * Get active
     *
     * @return integer 
     */
    public function getActive()
    {
        return $this->active;
    }

    public function getPictures()
    {
        return $this->pictures;
    }

    public function setPictures(ArrayCollection $pictures)
    {
        $this->pictures = $pictures;
    }
}