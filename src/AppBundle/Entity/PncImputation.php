<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PncImputation
 *
 * @ORM\Table(name="pnc_imputation", indexes={@ORM\Index(name="fk_pnc_imputation_pnc1_idx", columns={"pnc_id"}), @ORM\Index(name="fk_pnc_imputation_image1_idx", columns={"image_id"})})
 * @ORM\Entity
 */
class PncImputation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="valider", type="integer", nullable=true)
     */
    private $valider = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Pnc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pnc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pnc_id", referencedColumnName="id")
     * })
     */
    private $pnc;

    /**
     * @var \AppBundle\Entity\Image
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Image")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     * })
     */
    private $image;



    /**
     * Set valider
     *
     * @param integer $valider
     *
     * @return PncImputation
     */
    public function setValider($valider)
    {
        $this->valider = $valider;

        return $this;
    }

    /**
     * Get valider
     *
     * @return integer
     */
    public function getValider()
    {
        return $this->valider;
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
     * Set pnc
     *
     * @param \AppBundle\Entity\Pnc $pnc
     *
     * @return PncImputation
     */
    public function setPnc(\AppBundle\Entity\Pnc $pnc = null)
    {
        $this->pnc = $pnc;

        return $this;
    }

    /**
     * Get pnc
     *
     * @return \AppBundle\Entity\Pnc
     */
    public function getPnc()
    {
        return $this->pnc;
    }

    /**
     * Set image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return PncImputation
     */
    public function setImage(\AppBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \AppBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }
}
