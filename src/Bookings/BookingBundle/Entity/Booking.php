<?php

namespace Bookings\BookingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Booking
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Bookings\BookingBundle\Entity\BookingRepository")
 */
class Booking
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
     * @ORM\Column(name="clientID", type="integer")
     */
    private $clientID;

    /**
     * @var string
     *
     * @ORM\Column(name="startDate", type="string", length=10)
     */
    private $startDate;

    /**
     * @var string
     *
     * @ORM\Column(name="endDate", type="string", length=10)
     */
    private $endDate;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="ownerBooking", type="integer")
     */
    private $ownerBooking;

    /**
     * @var integer
     *
     * @ORM\Column(name="ownerConfirm", type="integer")
     */
    private $ownerConfirm;

    /**
     * @var integer
     *
     * @ORM\Column(name="fromiCalID", type="integer")
     */
    private $fromiCalID;


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
     * @return Booking
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
     * Set clientID
     *
     * @param integer $clientID
     * @return Booking
     */
    public function setClientID($clientID)
    {
        $this->clientID = $clientID;
    
        return $this;
    }

    /**
     * Get clientID
     *
     * @return integer 
     */
    public function getClientID()
    {
        return $this->clientID;
    }

    /**
     * Set startDate
     *
     * @param string $startDate
     * @return Booking
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    
        return $this;
    }

    /**
     * Get startDate
     *
     * @return string 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param string $endDate
     * @return Booking
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    
        return $this;
    }

    /**
     * Get endDate
     *
     * @return string 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Booking
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

    /**
     * Set status
     *
     * @param boolean $status
     * @return Booking
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return boolean 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set ownerBooking
     *
     * @param boolean $ownerBooking
     * @return Booking
     */
    public function setOwnerBooking($ownerBooking)
    {
        $this->ownerBooking = $ownerBooking;
    
        return $this;
    }

    /**
     * Get ownerBooking
     *
     * @return boolean 
     */
    public function getOwnerBooking()
    {
        return $this->ownerBooking;
    }

    /**
     * Set ownerConfirm
     *
     * @param integer $ownerConfirm
     * @return Booking
     */
    public function setOwnerConfirm($ownerConfirm)
    {
        $this->ownerConfirm = $ownerConfirm;
    
        return $this;
    }

    /**
     * Get ownerConfirm
     *
     * @return integer 
     */
    public function getOwnerConfirm()
    {
        return $this->ownerConfirm;
    }

    /**
     * Set fromiCalID
     *
     * @param integer $fromiCalID
     * @return Booking
     */
    public function setFromIcalID($icalID)
    {
        $this->fromiCalID = $icalID;

        return $this;
    }

    /**
     * Get fromiCalID
     *
     * @return integer
     */
    public function getFromIcalID()
    {
        return $this->fromiCalID;
    }
}
