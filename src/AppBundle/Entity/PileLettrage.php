<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PileLettrage
 *
 * @ORM\Table(name="pile_lettrage", indexes={@ORM\Index(name="fk_pile_lettrage_pcc_idx", columns={"pcc_id"}), @ORM\Index(name="fk_pile_lettrage_tiers_idx", columns={"tiers_id"})})
 * @ORM\Entity
 */
class PileLettrage
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_validation", type="date", nullable=true)
     */
    private $dateValidation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_devalidation", type="date", nullable=true)
     */
    private $dateDevalidation;

    /**
     * @var integer
     *
     * @ORM\Column(name="type_maj", type="integer", nullable=false)
     */
    private $typeMaj = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="lettre", type="string", length=5, nullable=false)
     */
    private $lettre = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Tiers
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Tiers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tiers_id", referencedColumnName="id")
     * })
     */
    private $tiers;

    /**
     * @var \AppBundle\Entity\Pcc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pcc_id", referencedColumnName="id")
     * })
     */
    private $pcc;



    /**
     * Set dateValidation
     *
     * @param \DateTime $dateValidation
     *
     * @return PileLettrage
     */
    public function setDateValidation($dateValidation)
    {
        $this->dateValidation = $dateValidation;

        return $this;
    }

    /**
     * Get dateValidation
     *
     * @return \DateTime
     */
    public function getDateValidation()
    {
        return $this->dateValidation;
    }

    /**
     * Set dateDevalidation
     *
     * @param \DateTime $dateDevalidation
     *
     * @return PileLettrage
     */
    public function setDateDevalidation($dateDevalidation)
    {
        $this->dateDevalidation = $dateDevalidation;

        return $this;
    }

    /**
     * Get dateDevalidation
     *
     * @return \DateTime
     */
    public function getDateDevalidation()
    {
        return $this->dateDevalidation;
    }

    /**
     * Set typeMaj
     *
     * @param integer $typeMaj
     *
     * @return PileLettrage
     */
    public function setTypeMaj($typeMaj)
    {
        $this->typeMaj = $typeMaj;

        return $this;
    }

    /**
     * Get typeMaj
     *
     * @return integer
     */
    public function getTypeMaj()
    {
        return $this->typeMaj;
    }

    /**
     * Set lettre
     *
     * @param string $lettre
     *
     * @return PileLettrage
     */
    public function setLettre($lettre)
    {
        $this->lettre = $lettre;

        return $this;
    }

    /**
     * Get lettre
     *
     * @return string
     */
    public function getLettre()
    {
        return $this->lettre;
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
     * Set tiers
     *
     * @param \AppBundle\Entity\Tiers $tiers
     *
     * @return PileLettrage
     */
    public function setTiers(\AppBundle\Entity\Tiers $tiers = null)
    {
        $this->tiers = $tiers;

        return $this;
    }

    /**
     * Get tiers
     *
     * @return \AppBundle\Entity\Tiers
     */
    public function getTiers()
    {
        return $this->tiers;
    }

    /**
     * Set pcc
     *
     * @param \AppBundle\Entity\Pcc $pcc
     *
     * @return PileLettrage
     */
    public function setPcc(\AppBundle\Entity\Pcc $pcc = null)
    {
        $this->pcc = $pcc;

        return $this;
    }

    /**
     * Get pcc
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getPcc()
    {
        return $this->pcc;
    }
}
