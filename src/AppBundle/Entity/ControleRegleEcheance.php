<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ControleRegleEcheance
 *
 * @ORM\Table(name="controle_regle_echeance", indexes={@ORM\Index(name="fk_reglechctrl_image_idx", columns={"image_id"})})
 * @ORM\Entity
 */
class ControleRegleEcheance
{
    /**
     * @var integer
     *
     * @ORM\Column(name="type_date", type="integer", nullable=true)
     */
    private $typeDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbre_jour", type="integer", nullable=true)
     */
    private $nbreJour;

    /**
     * @var integer
     *
     * @ORM\Column(name="date_le", type="integer", nullable=true)
     */
    private $dateLe;

    /**
     * @var integer
     *
     * @ORM\Column(name="type_tiers", type="integer", nullable=true)
     */
    private $typeTiers;

    /**
     * @var integer
     *
     * @ORM\Column(name="type_echeance", type="integer", nullable=true)
     */
    private $typeEcheance;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * Set typeDate
     *
     * @param integer $typeDate
     *
     * @return ControleRegleEcheance
     */
    public function setTypeDate($typeDate)
    {
        $this->typeDate = $typeDate;

        return $this;
    }

    /**
     * Get typeDate
     *
     * @return integer
     */
    public function getTypeDate()
    {
        return $this->typeDate;
    }

    /**
     * Set nbreJour
     *
     * @param integer $nbreJour
     *
     * @return ControleRegleEcheance
     */
    public function setNbreJour($nbreJour)
    {
        $this->nbreJour = $nbreJour;

        return $this;
    }

    /**
     * Get nbreJour
     *
     * @return integer
     */
    public function getNbreJour()
    {
        return $this->nbreJour;
    }

    /**
     * Set dateLe
     *
     * @param integer $dateLe
     *
     * @return ControleRegleEcheance
     */
    public function setDateLe($dateLe)
    {
        $this->dateLe = $dateLe;

        return $this;
    }

    /**
     * Get dateLe
     *
     * @return integer
     */
    public function getDateLe()
    {
        return $this->dateLe;
    }

    /**
     * Set typeTiers
     *
     * @param integer $typeTiers
     *
     * @return ControleRegleEcheance
     */
    public function setTypeTiers($typeTiers)
    {
        $this->typeTiers = $typeTiers;

        return $this;
    }

    /**
     * Get typeTiers
     *
     * @return integer
     */
    public function getTypeTiers()
    {
        return $this->typeTiers;
    }

    /**
     * Set typeEcheance
     *
     * @param integer $typeEcheance
     *
     * @return ControleRegleEcheance
     */
    public function setTypeEcheance($typeEcheance)
    {
        $this->typeEcheance = $typeEcheance;

        return $this;
    }

    /**
     * Get typeEcheance
     *
     * @return integer
     */
    public function getTypeEcheance()
    {
        return $this->typeEcheance;
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
     * Set image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return ControleRegleEcheance
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
