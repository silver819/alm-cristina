<?php

namespace User\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Users
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="User\UserBundle\Entity\UsersRepository")
 */
class Users extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank(message="Introduce tu nombre.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="Nombre demasiado corto.",
     *     maxMessage="Nombre demasiado largo.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=255, nullable=true)
     */
    protected $surname;

    /**
     * @var integer
     *
     * @ORM\Column(name="phoneNumber", type="integer", nullable=true)
     */
    protected $phoneNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="mobileNumber", type="integer", nullable=true)
     */
    protected $mobileNumber;

    /** @ORM\Column(name="facebook_id", type="string", length=255, nullable=true) */
    protected $facebook_id;

    /** @ORM\Column(name="facebook_access_token", type="string", length=255, nullable=true) */
    protected $facebook_access_token;

    /** @ORM\Column(name="google_id", type="string", length=255, nullable=true) */
    protected $google_id;

    /** @ORM\Column(name="google_access_token", type="string", length=255, nullable=true) */
    protected $google_access_token;

    public function __construct()
    {
        parent::__construct();

        $this->roles = array('ROLE_USER');
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
     * @return Users
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
     * Set surname
     *
     * @param string $surname
     * @return Users
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    
        return $this;
    }

    /**
     * Get surname
     *
     * @return string 
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set phoneNumber
     *
     * @param integer $phoneNumber
     * @return Users
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    
        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return integer 
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set mobileNumber
     *
     * @param integer $mobileNumber
     * @return Users
     */
    public function setMobileNumber($mobileNumber)
    {
        $this->mobileNumber = $mobileNumber;
    
        return $this;
    }

    /**
     * Get mobileNumber
     *
     * @return integer 
     */
    public function getMobileNumber()
    {
        return $this->mobileNumber;
    }

    public function getRole()
    {
        return $this->roles;
    }

    /**
     * Set FacebookID
     *
     * @param string $name
     * @return Users
     */
    public function setFacebookId($id)
    {
        $this->facebook_id = $id;
        return $this;
    }

    /**
     * Get FacebookID
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    /**
     * Set FacebookAccessToken
     *
     * @param string $name
     * @return Users
     */
    public function setFacebookAccessToken($token)
    {
        $this->facebook_access_token = $token;
        return $this;
    }

    /**
     * Get FacebookAccessToken
     *
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
    }

    /**
     * Set GoogleID
     *
     * @param string $name
     * @return Users
     */
    public function setGoogleId($id)
    {
        $this->google_id = $id;
        return $this;
    }

    /**
     * Get GoogleID
     *
     * @return string
     */
    public function getGoogleId()
    {
        return $this->google_id;
    }

    /**
     * Set FacebookAccessToken
     *
     * @param string $name
     * @return Users
     */
    public function setGoogleAccessToken($token)
    {
        $this->google_access_token = $token;
        return $this;
    }

    /**
     * Get GoogleAccessToken
     *
     * @return string
     */
    public function getGoogleAccessToken()
    {
        return $this->google_access_token;
    }
}
