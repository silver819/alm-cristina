<?php

namespace Reservable\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Picture
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Reservable\ActivityBundle\Entity\PictureRepository")
 */
class Picture
{
    const DIRECTORYIMAGES = "/var/www/almacen/web/images/properties";

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @Assert\File(maxSize="6000000")
     */
    protected $file;


    /**
    * @ORM\Column(type="integer")
    * @ORM\ManyToOne(targetEntity="Activity")
    * @ORM\JoinColumn(name="activityID", referencedColumnName="id")
    **/
    protected $activityID;

    public function setPath($name)
    {
        $this->path = $name;
    }

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().$this->path;
    }

    public function getPath(){
        return $this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'images/properties/';
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    public function upload($fileName)
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        $extension = $this->getFile()->guessExtension();
        if (!$extension) {
            $extension = 'bin';
        }
        $fileName .= "." . $extension;

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $this->getFile()->move($this->getUploadRootDir(), $fileName);

        // set the path property to the filename where you've saved the file
        // $this->path = $this->getFile()->getClientOriginalName();
        $this->path = $fileName;

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    /**
    * Set setActivityID
    *
    * @param string $activityID
    * @return self
    */
    public function setActivityID($activityID)
    {
        $this->activityID = $activityID;
        return $this;
    }

    /**
    * Get getActivityID
    *
    * @return string $activityID
    */
    public function getActivityID()
    {
        return $this->activityID;
    }

    /**
    * Set id
    *
    * @param string $id
    * @return self
    */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
    * Get id
    *
    * @return string $id
    */
    public function getId()
    {
        return $this->id;
    }
}
