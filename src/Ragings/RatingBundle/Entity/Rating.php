<?php

namespace Ragings\RatingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rating
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ragings\RatingBundle\Entity\RatingRepository")
 */
class Rating
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
     * @ORM\Column(name="reservationNumber", type="integer")
     */
    private $reservationNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="ubicacion", type="integer", nullable=true)
     */
    private $ubicacion;

    /**
     * @var integer
     *
     * @ORM\Column(name="llegar", type="integer", nullable=true)
     */
    private $llegar;

    /**
     * @var integer
     *
     * @ORM\Column(name="limpieza", type="integer", nullable=true)
     */
    private $limpieza;

    /**
     * @var integer
     *
     * @ORM\Column(name="material", type="integer", nullable=true)
     */
    private $material;

    /**
     * @var integer
     *
     * @ORM\Column(name="caracteristicas", type="integer", nullable=true)
     */
    private $caracteristicas;

    /**
     * @var integer
     *
     * @ORM\Column(name="gestiones", type="integer", nullable=true)
     */
    private $gestiones;

    /**
     * @var integer
     *
     * @ORM\Column(name="usabilidad", type="integer", nullable=true)
     */
    private $usabilidad;

    /**
     * @var integer
     *
     * @ORM\Column(name="repetir", type="integer", nullable=true)
     */
    private $repetir;

    /**
     * @var integer
     *
     * @ORM\Column(name="encontrar", type="integer", nullable=true)
     */
    private $encontrar;

    /**
     * @var integer
     *
     * @ORM\Column(name="recomendar", type="integer", nullable=true)
     */
    private $recomendar;

    /**
     * @var string
     *
     * @ORM\Column(name="mejoras", type="text", nullable=true)
     */
    private $mejoras;

    /**
     * @var string
     *
     * @ORM\Column(name="comentarios", type="text", nullable=true)
     */
    private $comentarios;


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
     * Set reservationNumber
     *
     * @param integer $reservationNumber
     * @return Rating
     */
    public function setReservationNumber($reservationNumber)
    {
        $this->reservationNumber = $reservationNumber;
    
        return $this;
    }

    /**
     * Get reservationNumber
     *
     * @return integer 
     */
    public function getReservationNumber()
    {
        return $this->reservationNumber;
    }

    /**
     * Set ubicacion
     *
     * @param integer $ubicacion
     * @return Rating
     */
    public function setUbicacion($ubicacion)
    {
        $this->ubicacion = $ubicacion;
    
        return $this;
    }

    /**
     * Get ubicacion
     *
     * @return integer 
     */
    public function getUbicacion()
    {
        return $this->ubicacion;
    }

    /**
     * Set llegar
     *
     * @param integer $llegar
     * @return Rating
     */
    public function setLlegar($llegar)
    {
        $this->llegar = $llegar;
    
        return $this;
    }

    /**
     * Get llegar
     *
     * @return integer 
     */
    public function getLlegar()
    {
        return $this->llegar;
    }

    /**
     * Set limpieza
     *
     * @param integer $limpieza
     * @return Rating
     */
    public function setLimpieza($limpieza)
    {
        $this->limpieza = $limpieza;
    
        return $this;
    }

    /**
     * Get limpieza
     *
     * @return integer 
     */
    public function getLimpieza()
    {
        return $this->limpieza;
    }

    /**
     * Set material
     *
     * @param integer $material
     * @return Rating
     */
    public function setMaterial($material)
    {
        $this->material = $material;
    
        return $this;
    }

    /**
     * Get material
     *
     * @return integer 
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * Set caracteristicas
     *
     * @param integer $caracteristicas
     * @return Rating
     */
    public function setCaracteristicas($caracteristicas)
    {
        $this->caracteristicas = $caracteristicas;
    
        return $this;
    }

    /**
     * Get caracteristicas
     *
     * @return integer 
     */
    public function getCaracteristicas()
    {
        return $this->caracteristicas;
    }

    /**
     * Set gestiones
     *
     * @param integer $gestiones
     * @return Rating
     */
    public function setGestiones($gestiones)
    {
        $this->gestiones = $gestiones;
    
        return $this;
    }

    /**
     * Get gestiones
     *
     * @return integer 
     */
    public function getGestiones()
    {
        return $this->gestiones;
    }

    /**
     * Set usabilidad
     *
     * @param integer $usabilidad
     * @return Rating
     */
    public function setUsabilidad($usabilidad)
    {
        $this->usabilidad = $usabilidad;
    
        return $this;
    }

    /**
     * Get usabilidad
     *
     * @return integer 
     */
    public function getUsabilidad()
    {
        return $this->usabilidad;
    }

    /**
     * Set repetir
     *
     * @param integer $repetir
     * @return Rating
     */
    public function setRepetir($repetir)
    {
        $this->repetir = $repetir;
    
        return $this;
    }

    /**
     * Get repetir
     *
     * @return integer 
     */
    public function getRepetir()
    {
        return $this->repetir;
    }

    /**
     * Set encontrar
     *
     * @param integer $encontrar
     * @return Rating
     */
    public function setEncontrar($encontrar)
    {
        $this->encontrar = $encontrar;
    
        return $this;
    }

    /**
     * Get encontrar
     *
     * @return integer 
     */
    public function getEncontrar()
    {
        return $this->encontrar;
    }

    /**
     * Set recomendar
     *
     * @param integer $recomendar
     * @return Rating
     */
    public function setRecomendar($recomendar)
    {
        $this->recomendar = $recomendar;
    
        return $this;
    }

    /**
     * Get recomendar
     *
     * @return integer 
     */
    public function getRecomendar()
    {
        return $this->recomendar;
    }

    /**
     * Set mejoras
     *
     * @param string $mejoras
     * @return Rating
     */
    public function setMejoras($mejoras)
    {
        $this->mejoras = $mejoras;
    
        return $this;
    }

    /**
     * Get mejoras
     *
     * @return string 
     */
    public function getMejoras()
    {
        return $this->mejoras;
    }

    /**
     * Set comentarios
     *
     * @param string $comentarios
     * @return Rating
     */
    public function setComentarios($comentarios)
    {
        $this->comentarios = $comentarios;
    
        return $this;
    }

    /**
     * Get comentarios
     *
     * @return string 
     */
    public function getComentarios()
    {
        return $this->comentarios;
    }
}
