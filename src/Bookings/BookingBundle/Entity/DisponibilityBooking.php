<?php

namespace Bookings\BookingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DisponibilityBooking
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Bookings\BookingBundle\Entity\DisponibilityBookingRepository")
 */
class DisponibilityBooking
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
     * @ORM\Column(name="bookingID", type="integer")
     */
    private $bookingID;

    /**
     * @var string
     *
     * @ORM\Column(name="date", type="string", length=10)
     */
    private $date;


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
     * Set bookingID
     *
     * @param integer $bookingID
     * @return DisponibilityBooking
     */
    public function setBookingID($bookingID)
    {
        $this->bookingID = $bookingID;
    
        return $this;
    }

    /**
     * Get bookingID
     *
     * @return integer 
     */
    public function getBookingID()
    {
        return $this->bookingID;
    }

    /**
     * Set date
     *
     * @param string $date
     * @return DisponibilityBooking
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return string 
     */
    public function getDate()
    {
        return $this->date;
    }
}
