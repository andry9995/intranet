<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TacheEntityLibreAction
 *
 * @ORM\Table(name="tache_entity_libre_action", indexes={@ORM\Index(name="fk_tache_entity_libre_action_tache_entity_idx", columns={"tache_entity_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TacheEntityLibreActionRepository")
 */
class TacheEntityLibreAction
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="demarrage", type="date", nullable=true)
     */
    private $demarrage;

    /**
     * @var integer
     *
     * @ORM\Column(name="periode", type="integer", nullable=false)
     */
    private $periode;

    /**
     * @var integer
     *
     * @ORM\Column(name="jour", type="integer", nullable=false)
     */
    private $jour = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="calculer_a_partir", type="integer", nullable=false)
     */
    private $calculerAPartir = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_calcul", type="date", nullable=true)
     */
    private $dateCalcul;

    /**
     * @var integer
     *
     * @ORM\Column(name="jour_semaine", type="integer", nullable=true)
     */
    private $jourSemaine = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="jalon", type="integer", nullable=false)
     */
    private $jalon = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="additif_jour", type="integer", nullable=false)
     */
    private $additifJour = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="mois_additif", type="integer", nullable=false)
     */
    private $moisAdditif = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="responsable", type="integer", nullable=false)
     */
    private $responsable = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\TacheEntity
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TacheEntity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_entity_id", referencedColumnName="id")
     * })
     */
    private $tacheEntity;



    /**
     * Set demarrage
     *
     * @param \DateTime $demarrage
     *
     * @return TacheEntityLibreAction
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
     * Set periode
     *
     * @param integer $periode
     *
     * @return TacheEntityLibreAction
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
     * Set jour
     *
     * @param integer $jour
     *
     * @return TacheEntityLibreAction
     */
    public function setJour($jour)
    {
        $this->jour = $jour;

        return $this;
    }

    /**
     * Get jour
     *
     * @return integer
     */
    public function getJour()
    {
        return $this->jour;
    }

    /**
     * Set calculerAPartir
     *
     * @param integer $calculerAPartir
     *
     * @return TacheEntityLibreAction
     */
    public function setCalculerAPartir($calculerAPartir)
    {
        $this->calculerAPartir = $calculerAPartir;

        return $this;
    }

    /**
     * Get calculerAPartir
     *
     * @return integer
     */
    public function getCalculerAPartir()
    {
        return $this->calculerAPartir;
    }

    /**
     * Set dateCalcul
     *
     * @param \DateTime $dateCalcul
     *
     * @return TacheEntityLibreAction
     */
    public function setDateCalcul($dateCalcul)
    {
        $this->dateCalcul = $dateCalcul;

        return $this;
    }

    /**
     * Get dateCalcul
     *
     * @return \DateTime
     */
    public function getDateCalcul()
    {
        return $this->dateCalcul;
    }

    /**
     * Set jourSemaine
     *
     * @param integer $jourSemaine
     *
     * @return TacheEntityLibreAction
     */
    public function setJourSemaine($jourSemaine)
    {
        $this->jourSemaine = $jourSemaine;

        return $this;
    }

    /**
     * Get jourSemaine
     *
     * @return integer
     */
    public function getJourSemaine()
    {
        return $this->jourSemaine;
    }

    /**
     * Set jalon
     *
     * @param integer $jalon
     *
     * @return TacheEntityLibreAction
     */
    public function setJalon($jalon)
    {
        $this->jalon = $jalon;

        return $this;
    }

    /**
     * Get jalon
     *
     * @return integer
     */
    public function getJalon()
    {
        return $this->jalon;
    }

    /**
     * Set additifJour
     *
     * @param integer $additifJour
     *
     * @return TacheEntityLibreAction
     */
    public function setAdditifJour($additifJour)
    {
        $this->additifJour = $additifJour;

        return $this;
    }

    /**
     * Get additifJour
     *
     * @return integer
     */
    public function getAdditifJour()
    {
        return $this->additifJour;
    }

    /**
     * Set moisAdditif
     *
     * @param integer $moisAdditif
     *
     * @return TacheEntityLibreAction
     */
    public function setMoisAdditif($moisAdditif)
    {
        $this->moisAdditif = $moisAdditif;

        return $this;
    }

    /**
     * Get moisAdditif
     *
     * @return integer
     */
    public function getMoisAdditif()
    {
        return $this->moisAdditif;
    }

    /**
     * Set responsable
     *
     * @param integer $responsable
     *
     * @return TacheEntityLibreAction
     */
    public function setResponsable($responsable)
    {
        $this->responsable = $responsable;

        return $this;
    }

    /**
     * Get responsable
     *
     * @return integer
     */
    public function getResponsable()
    {
        return $this->responsable;
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
     * Set tacheEntity
     *
     * @param \AppBundle\Entity\TacheEntity $tacheEntity
     *
     * @return TacheEntityLibreAction
     */
    public function setTacheEntity(\AppBundle\Entity\TacheEntity $tacheEntity = null)
    {
        $this->tacheEntity = $tacheEntity;

        return $this;
    }

    /**
     * Get tacheEntity
     *
     * @return \AppBundle\Entity\TacheEntity
     */
    public function getTacheEntity()
    {
        return $this->tacheEntity;
    }
}
