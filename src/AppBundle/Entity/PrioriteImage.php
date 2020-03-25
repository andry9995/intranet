<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrioriteImage
 *
 * @ORM\Table(name="priorite_image")
 * @ORM\Entity
 */
class PrioriteImage
{
    /**
     * @var integer
     *
     * @ORM\Column(name="image_id", type="integer", nullable=false)
     */
    private $imageId;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set imageId
     *
     * @param integer $imageId
     *
     * @return PrioriteImage
     */
    public function setImageId($imageId)
    {
        $this->imageId = $imageId;

        return $this;
    }

    /**
     * Get imageId
     *
     * @return integer
     */
    public function getImageId()
    {
        return $this->imageId;
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
}
