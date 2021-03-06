<?php

namespace Reservable\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TypeActivity
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Reservable\ActivityBundle\Entity\TypeActivityRepository")
 */
class TypeActivity
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string", length=255)
     */
    private $mode;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=255)
     */
    private $icon;

    /**
     * @var string
     *
     * @ORM\Column(name="es", type="string", length=255)
     */
    private $es;

    /**
     * @var string
     *
     * @ORM\Column(name="en", type="string", length=255)
     */
    private $en;


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
     * @return TypeActivity
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
     * Set mode
     *
     * @param string $mode
     * @return TypeActivity
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Get mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get mode
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    public function setEs($es)
    {
        $this->es = $es;

        return $this;
    }

    /**
     * Get mode
     *
     * @return string
     */
    public function getEs()
    {
        return $this->es;
    }

    public function setEn($en)
    {
        $this->en = $en;

        return $this;
    }

    /**
     * Get mode
     *
     * @return string
     */
    public function getEn()
    {
        return $this->en;
    }
}
