<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TacheClient
 *
 * @ORM\Table(name="tache_client", indexes={@ORM\Index(name="fk_tache_client_tache_liste1_idx", columns={"tache_id"}), @ORM\Index(name="fk_tache_client_client1_idx", columns={"client_id"}), @ORM\Index(name="fk_tache_client_utilisateur_idx", columns={"responsable_client"}), @ORM\Index(name="fk_tache_client_operateur_idx", columns={"responsable_scriptura"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TacheClientRepository")
 */
class TacheClient
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
     * @var \AppBundle\Entity\Client
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     * })
     */
    private $client;

    /**
     * Set periode
     *
     * @param integer $periode
     *
     * @return TacheClient
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
     * @return TacheClient
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
     * @return TacheClient
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
     * @return TacheClient
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
     * @return TacheClient
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
     * @return TacheClient
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
     * @return TacheClient
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
     * @return TacheClient
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
     * @return TacheClient
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
     * @return TacheClient
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
     * @return TacheClient
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
     * @return TacheClient
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
     * Set client
     *
     * @param \AppBundle\Entity\Client $client
     *
     * @return TacheClient
     */
    public function setClient(\AppBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \AppBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
