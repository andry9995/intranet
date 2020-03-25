<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PileItem
 *
 * @ORM\Table(name="pile_item", indexes={@ORM\Index(name="fk_pile_item_pile_lettrage_idx", columns={"pile_lettrage_id"}), @ORM\Index(name="fk_pile_item_image_idx", columns={"image_id"}), @ORM\Index(name="fk_pile_item_releve_idx", columns={"releve_id"}), @ORM\Index(name="fk_pile_item_ecriture_idx", columns={"ecriture_id"})})
 * @ORM\Entity
 */
class PileItem
{
    /**
     * @var string
     *
     * @ORM\Column(name="image_str", type="string", length=250, nullable=true)
     */
    private $imageStr;

    /**
     * @var integer
     *
     * @ORM\Column(name="valide", type="integer", nullable=false)
     */
    private $valide = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Releve
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Releve")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="releve_id", referencedColumnName="id")
     * })
     */
    private $releve;

    /**
     * @var \AppBundle\Entity\PileLettrage
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PileLettrage")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pile_lettrage_id", referencedColumnName="id")
     * })
     */
    private $pileLettrage;

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
     * @var \AppBundle\Entity\Ecriture
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Ecriture")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecriture_id", referencedColumnName="id")
     * })
     */
    private $ecriture;



    /**
     * Set imageStr
     *
     * @param string $imageStr
     *
     * @return PileItem
     */
    public function setImageStr($imageStr)
    {
        $this->imageStr = $imageStr;

        return $this;
    }

    /**
     * Get imageStr
     *
     * @return string
     */
    public function getImageStr()
    {
        return $this->imageStr;
    }

    /**
     * Set valide
     *
     * @param integer $valide
     *
     * @return PileItem
     */
    public function setValide($valide)
    {
        $this->valide = $valide;

        return $this;
    }

    /**
     * Get valide
     *
     * @return integer
     */
    public function getValide()
    {
        return $this->valide;
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
     * Set releve
     *
     * @param \AppBundle\Entity\Releve $releve
     *
     * @return PileItem
     */
    public function setReleve(\AppBundle\Entity\Releve $releve = null)
    {
        $this->releve = $releve;

        return $this;
    }

    /**
     * Get releve
     *
     * @return \AppBundle\Entity\Releve
     */
    public function getReleve()
    {
        return $this->releve;
    }

    /**
     * Set pileLettrage
     *
     * @param \AppBundle\Entity\PileLettrage $pileLettrage
     *
     * @return PileItem
     */
    public function setPileLettrage(\AppBundle\Entity\PileLettrage $pileLettrage = null)
    {
        $this->pileLettrage = $pileLettrage;

        return $this;
    }

    /**
     * Get pileLettrage
     *
     * @return \AppBundle\Entity\PileLettrage
     */
    public function getPileLettrage()
    {
        return $this->pileLettrage;
    }

    /**
     * Set image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return PileItem
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

    /**
     * Set ecriture
     *
     * @param \AppBundle\Entity\Ecriture $ecriture
     *
     * @return PileItem
     */
    public function setEcriture(\AppBundle\Entity\Ecriture $ecriture = null)
    {
        $this->ecriture = $ecriture;

        return $this;
    }

    /**
     * Get ecriture
     *
     * @return \AppBundle\Entity\Ecriture
     */
    public function getEcriture()
    {
        return $this->ecriture;
    }
}
