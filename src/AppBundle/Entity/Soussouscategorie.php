<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Soussouscategorie
 *
 * @ORM\Table(name="soussouscategorie", indexes={@ORM\Index(name="fk_soussouscategorie_souscategorie1_idx", columns={"souscategorie_id"}), @ORM\Index(name="fk_sscat_sousnature_id_idx", columns={"sousnature_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SoussouscategorieRepository")
 */
class Soussouscategorie
{
    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=250, nullable=false)
     */
    private $libelle = '';

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_new", type="string", length=250, nullable=true)
     */
    private $libelleNew = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="multi_exercice", type="integer", nullable=true)
     */
    private $multiExercice = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="dossier_permanent", type="integer", nullable=true)
     */
    private $dossierPermanent = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="actif", type="integer", nullable=true)
     */
    private $actif = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Sousnature
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Sousnature")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sousnature_id", referencedColumnName="id")
     * })
     */
    private $sousnature;

    /**
     * @var \AppBundle\Entity\Souscategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Souscategorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="souscategorie_id", referencedColumnName="id")
     * })
     */
    private $souscategorie;



    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Soussouscategorie
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
     * Set libelleNew
     *
     * @param string $libelleNew
     *
     * @return Soussouscategorie
     */
    public function setLibelleNew($libelleNew)
    {
        $this->libelleNew = $libelleNew;

        return $this;
    }

    /**
     * Get libelleNew
     *
     * @return string
     */
    public function getLibelleNew()
    {
        return $this->libelleNew;
    }

    /**
     * Set multiExercice
     *
     * @param integer $multiExercice
     *
     * @return Soussouscategorie
     */
    public function setMultiExercice($multiExercice)
    {
        $this->multiExercice = $multiExercice;

        return $this;
    }

    /**
     * Get multiExercice
     *
     * @return integer
     */
    public function getMultiExercice()
    {
        return $this->multiExercice;
    }

    /**
     * Set dossierPermanent
     *
     * @param integer $dossierPermanent
     *
     * @return Soussouscategorie
     */
    public function setDossierPermanent($dossierPermanent)
    {
        $this->dossierPermanent = $dossierPermanent;

        return $this;
    }

    /**
     * Get dossierPermanent
     *
     * @return integer
     */
    public function getDossierPermanent()
    {
        return $this->dossierPermanent;
    }

    /**
     * Set actif
     *
     * @param integer $actif
     *
     * @return Soussouscategorie
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get actif
     *
     * @return integer
     */
    public function getActif()
    {
        return $this->actif;
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
     * Set sousnature
     *
     * @param \AppBundle\Entity\Sousnature $sousnature
     *
     * @return Soussouscategorie
     */
    public function setSousnature(\AppBundle\Entity\Sousnature $sousnature = null)
    {
        $this->sousnature = $sousnature;

        return $this;
    }

    /**
     * Get sousnature
     *
     * @return \AppBundle\Entity\Sousnature
     */
    public function getSousnature()
    {
        return $this->sousnature;
    }

    /**
     * Set souscategorie
     *
     * @param \AppBundle\Entity\Souscategorie $souscategorie
     *
     * @return Soussouscategorie
     */
    public function setSouscategorie(\AppBundle\Entity\Souscategorie $souscategorie = null)
    {
        $this->souscategorie = $souscategorie;

        return $this;
    }

    /**
     * Get souscategorie
     *
     * @return \AppBundle\Entity\Souscategorie
     */
    public function getSouscategorie()
    {
        return $this->souscategorie;
    }
}
