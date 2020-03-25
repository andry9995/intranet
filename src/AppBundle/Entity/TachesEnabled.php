<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TachesEnabled
 *
 * @ORM\Table(name="taches_enabled", indexes={@ORM\Index(name="fk_taches_enabled_idx", columns={"client_id"}), @ORM\Index(name="fk_taches_enabled_dossier_idx", columns={"dossier_id"}), @ORM\Index(name="fk_taches_enabled_taches_idx", columns={"taches_id"}), @ORM\Index(name="fk_taches_enabled_idx1", columns={"taches_item_id"}), @ORM\Index(name="fk_taches_enabled_tache_action_idx", columns={"taches_action_id"}), @ORM\Index(name="fk_taches_enabled_taches_date_idx", columns={"taches_date_id"})})
 * @ORM\Entity
 */
class TachesEnabled
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\TachesItem
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TachesItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="taches_item_id", referencedColumnName="id")
     * })
     */
    private $tachesItem;

    /**
     * @var \AppBundle\Entity\TachesAction
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TachesAction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="taches_action_id", referencedColumnName="id")
     * })
     */
    private $tachesAction;

    /**
     * @var \AppBundle\Entity\Taches
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Taches")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="taches_id", referencedColumnName="id")
     * })
     */
    private $taches;

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
     * @var \AppBundle\Entity\TachesDate
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TachesDate")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="taches_date_id", referencedColumnName="id")
     * })
     */
    private $tachesDate;



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
     * Set tachesItem
     *
     * @param \AppBundle\Entity\TachesItem $tachesItem
     *
     * @return TachesEnabled
     */
    public function setTachesItem(\AppBundle\Entity\TachesItem $tachesItem = null)
    {
        $this->tachesItem = $tachesItem;

        return $this;
    }

    /**
     * Get tachesItem
     *
     * @return \AppBundle\Entity\TachesItem
     */
    public function getTachesItem()
    {
        return $this->tachesItem;
    }

    /**
     * Set tachesAction
     *
     * @param \AppBundle\Entity\TachesAction $tachesAction
     *
     * @return TachesEnabled
     */
    public function setTachesAction(\AppBundle\Entity\TachesAction $tachesAction = null)
    {
        $this->tachesAction = $tachesAction;

        return $this;
    }

    /**
     * Get tachesAction
     *
     * @return \AppBundle\Entity\TachesAction
     */
    public function getTachesAction()
    {
        return $this->tachesAction;
    }

    /**
     * Set taches
     *
     * @param \AppBundle\Entity\Taches $taches
     *
     * @return TachesEnabled
     */
    public function setTaches(\AppBundle\Entity\Taches $taches = null)
    {
        $this->taches = $taches;

        return $this;
    }

    /**
     * Get taches
     *
     * @return \AppBundle\Entity\Taches
     */
    public function getTaches()
    {
        return $this->taches;
    }

    /**
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return TachesEnabled
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
     * @return TachesEnabled
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

    /**
     * Set tachesDate
     *
     * @param \AppBundle\Entity\TachesDate $tachesDate
     *
     * @return TachesEnabled
     */
    public function setTachesDate(\AppBundle\Entity\TachesDate $tachesDate = null)
    {
        $this->tachesDate = $tachesDate;

        return $this;
    }

    /**
     * Get tachesDate
     *
     * @return \AppBundle\Entity\TachesDate
     */
    public function getTachesDate()
    {
        return $this->tachesDate;
    }
}
