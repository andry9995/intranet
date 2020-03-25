<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TacheEntity
 *
 * @ORM\Table(name="tache_entity", indexes={@ORM\Index(name="fk_tache_entity_client_1_idx", columns={"client_id"}), @ORM\Index(name="fk_tache_entity_dossier_1_idx", columns={"dossier_id"}), @ORM\Index(name="fk_tache_entity_tache_1_idx", columns={"tache_id"}), @ORM\Index(name="fk_tache_entity_tache_legale_1_idx", columns={"tache_legale_id"}), @ORM\Index(name="fk_tache_entity_tache_entity_idx", columns={"tache_entity_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TacheEntityRepository")
 */
class TacheEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="desactiver", type="integer", nullable=false)
     */
    private $desactiver = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\TacheLegale
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TacheLegale")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_legale_id", referencedColumnName="id")
     * })
     */
    private $tacheLegale;

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
     * @var \AppBundle\Entity\Tache
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Tache")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_id", referencedColumnName="id")
     * })
     */
    private $tache;

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
     * @var \AppBundle\Entity\Client
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     * })
     */
    private $client;



    /**
     * Set desactiver
     *
     * @param integer $desactiver
     *
     * @return TacheEntity
     */
    public function setDesactiver($desactiver)
    {
        $this->desactiver = $desactiver;

        return $this;
    }

    /**
     * Get desactiver
     *
     * @return integer
     */
    public function getDesactiver()
    {
        return $this->desactiver;
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
     * Set tacheLegale
     *
     * @param \AppBundle\Entity\TacheLegale $tacheLegale
     *
     * @return TacheEntity
     */
    public function setTacheLegale(\AppBundle\Entity\TacheLegale $tacheLegale = null)
    {
        $this->tacheLegale = $tacheLegale;

        return $this;
    }

    /**
     * Get tacheLegale
     *
     * @return \AppBundle\Entity\TacheLegale
     */
    public function getTacheLegale()
    {
        return $this->tacheLegale;
    }

    /**
     * Set tacheEntity
     *
     * @param \AppBundle\Entity\TacheEntity $tacheEntity
     *
     * @return TacheEntity
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

    /**
     * Set tache
     *
     * @param \AppBundle\Entity\Tache $tache
     *
     * @return TacheEntity
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
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return TacheEntity
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

    /**
     * Set client
     *
     * @param \AppBundle\Entity\Client $client
     *
     * @return TacheEntity
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
