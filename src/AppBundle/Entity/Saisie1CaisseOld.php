<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Saisie1CaisseOld
 *
 * @ORM\Table(name="saisie1_caisse_old", indexes={@ORM\Index(name="fk_saisie1_caisse_image1_idx", columns={"image_id"})})
 * @ORM\Entity
 */
class Saisie1CaisseOld
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var float
     *
     * @ORM\Column(name="solde_initial", type="float", precision=10, scale=0, nullable=true)
     */
    private $soldeInitial;

    /**
     * @var float
     *
     * @ORM\Column(name="encaissement_tva1", type="float", precision=10, scale=0, nullable=true)
     */
    private $encaissementTva1;

    /**
     * @var float
     *
     * @ORM\Column(name="encaissement_tva2", type="float", precision=10, scale=0, nullable=true)
     */
    private $encaissementTva2;

    /**
     * @var float
     *
     * @ORM\Column(name="depot_banque", type="float", precision=10, scale=0, nullable=true)
     */
    private $depotBanque;

    /**
     * @var float
     *
     * @ORM\Column(name="achat", type="float", precision=10, scale=0, nullable=true)
     */
    private $achat;

    /**
     * @var float
     *
     * @ORM\Column(name="retrait", type="float", precision=10, scale=0, nullable=true)
     */
    private $retrait;

    /**
     * @var float
     *
     * @ORM\Column(name="solde_final", type="float", precision=10, scale=0, nullable=true)
     */
    private $soldeFinal;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=100, nullable=true)
     */
    private $libelle;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Saisie1CaisseOld
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set soldeInitial
     *
     * @param float $soldeInitial
     *
     * @return Saisie1CaisseOld
     */
    public function setSoldeInitial($soldeInitial)
    {
        $this->soldeInitial = $soldeInitial;

        return $this;
    }

    /**
     * Get soldeInitial
     *
     * @return float
     */
    public function getSoldeInitial()
    {
        return $this->soldeInitial;
    }

    /**
     * Set encaissementTva1
     *
     * @param float $encaissementTva1
     *
     * @return Saisie1CaisseOld
     */
    public function setEncaissementTva1($encaissementTva1)
    {
        $this->encaissementTva1 = $encaissementTva1;

        return $this;
    }

    /**
     * Get encaissementTva1
     *
     * @return float
     */
    public function getEncaissementTva1()
    {
        return $this->encaissementTva1;
    }

    /**
     * Set encaissementTva2
     *
     * @param float $encaissementTva2
     *
     * @return Saisie1CaisseOld
     */
    public function setEncaissementTva2($encaissementTva2)
    {
        $this->encaissementTva2 = $encaissementTva2;

        return $this;
    }

    /**
     * Get encaissementTva2
     *
     * @return float
     */
    public function getEncaissementTva2()
    {
        return $this->encaissementTva2;
    }

    /**
     * Set depotBanque
     *
     * @param float $depotBanque
     *
     * @return Saisie1CaisseOld
     */
    public function setDepotBanque($depotBanque)
    {
        $this->depotBanque = $depotBanque;

        return $this;
    }

    /**
     * Get depotBanque
     *
     * @return float
     */
    public function getDepotBanque()
    {
        return $this->depotBanque;
    }

    /**
     * Set achat
     *
     * @param float $achat
     *
     * @return Saisie1CaisseOld
     */
    public function setAchat($achat)
    {
        $this->achat = $achat;

        return $this;
    }

    /**
     * Get achat
     *
     * @return float
     */
    public function getAchat()
    {
        return $this->achat;
    }

    /**
     * Set retrait
     *
     * @param float $retrait
     *
     * @return Saisie1CaisseOld
     */
    public function setRetrait($retrait)
    {
        $this->retrait = $retrait;

        return $this;
    }

    /**
     * Get retrait
     *
     * @return float
     */
    public function getRetrait()
    {
        return $this->retrait;
    }

    /**
     * Set soldeFinal
     *
     * @param float $soldeFinal
     *
     * @return Saisie1CaisseOld
     */
    public function setSoldeFinal($soldeFinal)
    {
        $this->soldeFinal = $soldeFinal;

        return $this;
    }

    /**
     * Get soldeFinal
     *
     * @return float
     */
    public function getSoldeFinal()
    {
        return $this->soldeFinal;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Saisie1CaisseOld
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
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
     * @return Saisie1CaisseOld
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
