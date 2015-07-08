<?php

namespace Reservable\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TypeToFeature
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Reservable\ActivityBundle\Entity\TypeToFeatureRepository")
 */
class TypeToFeature
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
     * @ORM\Column(name="typeID", type="integer")
     */
    private $typeID;

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
     * Set typeID
     *
     * @param integer $typeID
     * @return TypeToFeature
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

    /**
     * Set featureID
     *
     * @param integer $featureID
     * @return TypeToFeature
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
