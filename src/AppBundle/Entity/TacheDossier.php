<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TacheDossier
 *
 * @ORM\Table(name="tache_dossier", indexes={@ORM\Index(name="tache_dossier_tache_idx", columns={"tache_id"}), @ORM\Index(name="tache_dossier_dossier_idx", columns={"dossier_id"}), @ORM\Index(name="tache_dossier_operateur_idx", columns={"responsable_scriptura"}), @ORM\Index(name="tache_dossier_utilisateur_idx", columns={"responsable_client"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TacheDossierRepository")
 */
class TacheDossier
{
    /**
     * @var integer
     *
     * @ORM\Column(name="periode", type="integer", nullable=false)
     */
    private $periode;

    /**
     * @var array
     *
     * @ORM\Column(name="date_list", type="simple_array", nullable=true)
     */
    private $dateList;

    /**
     * @var integer
     *
     * @ORM\Column(name="mois_plus", type="integer", nullable=false)
     */
    private $moisPlus = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="plus_tard", type="integer", nullable=false)
     */
    private $plusTard;

    /**
     * @var integer
     *
     * @ORM\Column(name="realiser_avant", type="integer", nullable=false)
     */
    private $realiserAvant = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="entite", type="integer", nullable=true)
     */
    private $entite;

    /**
     * @var boolean
     *
     * @ORM\Column(name="jalon", type="boolean", nullable=false)
     */
    private $jalon = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="demarrage", type="date", nullable=false)
     */
    private $demarrage;

    /**
     * @var boolean
     *
     * @ORM\Column(name="legale", type="boolean", nullable=false)
     */
    private $legale = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="responsable_client", referencedColumnName="id")
     * })
     */
    private $responsableClient;

    /**
     * @var \AppBundle\Entity\Tache
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Tache")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_id", referencedColumnName="id")
     * })
     */
    private $tache;

    /**
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="responsable_scriptura", referencedColumnName="id")
     * })
     */
    private $responsableScriptura;

    /**
     * @var \AppBundle\Entity\Dossier
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Dossier")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dossier_id", referencedColumnName="id")
     * })
     */
    private $dossier;

    /**
     * Set periode
     *
     * @param integer $periode
     *
     * @return TacheDossier
     */
    public function setPeriode($periode)
    {
        $this->periode = $periode;

        return $this;
    }

    /**
     * Get periode
     *
     * @return integer
     */
    public function getPeriode()
    {
        return $this->periode;
    }

    /**
     * Set dateList
     *
     * @param array $dateList
     *
     * @return TacheDossier
     */
    public function setDateList($dateList)
    {
        $this->dateList = $dateList;

        return $this;
    }

    /**
     * Get dateList
     *
     * @return array
     */
    public function getDateList()
    {
        return $this->dateList;
    }

    /**
     * Set moisPlus
     *
     * @param integer $moisPlus
     *
     * @return TacheDossier
     */
    public function setMoisPlus($moisPlus)
    {
        $this->moisPlus = $moisPlus;

        return $this;
    }

    /**
     * Get moisPlus
     *
     * @return integer
     */
    public function getMoisPlus()
    {
        return $this->moisPlus;
    }

    /**
     * Set plusTard
     *
     * @param integer $plusTard
     *
     * @return TacheDossier
     */
    public function setPlusTard($plusTard)
    {
        $this->plusTard = $plusTard;

        return $this;
    }

    /**
     * Get plusTard
     *
     * @return integer
     */
    public function getPlusTard()
    {
        return $this->plusTard;
    }

    /**
     * Set realiserAvant
     *
     * @param integer $realiserAvant
     *
     * @return TacheDossier
     */
    public function setRealiserAvant($realiserAvant)
    {
        $this->realiserAvant = $realiserAvant;

        return $this;
    }

    /**
     * Get realiserAvant
     *
     * @return integer
     */
    public function getRealiserAvant()
    {
        return $this->realiserAvant;
    }

    /**
     * Set entite
     *
     * @param integer $entite
     *
     * @return TacheDossier
     */
    public function setEntite($entite)
    {
        $this->entite = $entite;

        return $this;
    }

    /**
     * Get entite
     *
     * @return integer
     */
    public function getEntite()
    {
        return $this->entite;
    }

    /**
     * Set jalon
     *
     * @param boolean $jalon
     *
     * @return TacheDossier
     */
    public function setJalon($jalon)
    {
        $this->jalon = $jalon;

        return $this;
    }

    /**
     * Get jalon
     *
     * @return boolean
     */
    public function getJalon()
    {
        return $this->jalon;
    }

    /**
     * Set demarrage
     *
     * @param \DateTime $demarrage
     *
     * @return TacheDossier
     */
    public function setDemarrage($demarrage)
    {
        $this->demarrage = $demarrage;

        return $this;
    }

    /**
     * Get demarrage
     *
     * @return \DateTime
     */
    public function getDemarrage()
    {
        return $this->demarrage;
    }

    /**
     * Set legale
     *
     * @param boolean $legale
     *
     * @return TacheDossier
     */
    public function setLegale($legale)
    {
        $this->legale = $legale;

        return $this;
    }

    /**
     * Get legale
     *
     * @return boolean
     */
    public function getLegale()
    {
        return $this->legale;
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
     * Set responsableClient
     *
     * @param \AppBundle\Entity\Utilisateur $responsableClient
     *
     * @return TacheDossier
     */
    public function setResponsableClient(\AppBundle\Entity\Utilisateur $responsableClient = null)
    {
        $this->responsableClient = $responsableClient;

        return $this;
    }

    /**
     * Get responsableClient
     *
     * @return \AppBundle\Entity\Utilisateur
     */
    public function getResponsableClient()
    {
        return $this->responsableClient;
    }

    /**
     * Set tache
     *
     * @param \AppBundle\Entity\Tache $tache
     *
     * @return TacheDossier
     */
    public function setTache(\AppBundle\Entity\Tache $tache = null)
    {
        $this->tache = $tache;

        return $this;
    }

    /**
     * Get tache
     *
     * @return \AppBundle\Entity\Tache
     */
    public function getTache()
    {
        return $this->tache;
    }

    /**
     * Set responsableScriptura
     *
     * @param \AppBundle\Entity\Operateur $responsableScriptura
     *
     * @return TacheDossier
     */
    public function setResponsableScriptura(\AppBundle\Entity\Operateur $responsableScriptura = null)
    {
        $this->responsableScriptura = $responsableScriptura;

        return $this;
    }

    /**
     * Get responsableScriptura
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getResponsableScriptura()
    {
        return $this->responsableScriptura;
    }

    /**
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return TacheDossier
     */
    public function setDossier(\AppBundle\Entity\Dossier $dossier = null)
    {
        $this->dossier = $dossier;

        return $this;
    }

    /**
     * Get dossier
     *
     * @return \AppBundle\Entity\Dossier
     */
    public function getDossier()
    {
        return $this->dossier;
    }
}
