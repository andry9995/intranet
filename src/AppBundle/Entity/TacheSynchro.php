<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TacheSynchro
 *
 * @ORM\Table(name="tache_synchro", indexes={@ORM\Index(name="fk_tache_synchro_dossier_idx", columns={"dossier_id"}), @ORM\Index(name="fk_tache_synchro_tache_entity_legale_action_idx", columns={"tache_entity_legale_action_id"}), @ORM\Index(name="fk_tache_synchro_tache_entity_libre_action_idx", columns={"tache_entity_libre_action_id"})})
 * @ORM\Entity
 */
class TacheSynchro
{
    /**
     * @var string
     *
     * @ORM\Column(name="id_google", type="string", length=45, nullable=false)
     */
    private $idGoogle;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datefait", type="date", nullable=true)
     */
    private $datefait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\TacheEntityLibreAction
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TacheEntityLibreAction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_entity_libre_action_id", referencedColumnName="id")
     * })
     */
    private $tacheEntityLibreAction;

    /**
     * @var \AppBundle\Entity\TacheEntityLegaleAction
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TacheEntityLegaleAction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_entity_legale_action_id", referencedColumnName="id")
     * })
     */
    private $tacheEntityLegaleAction;

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
     * Set idGoogle
     *
     * @param string $idGoogle
     *
     * @return TacheSynchro
     */
    public function setIdGoogle($idGoogle)
    {
        $this->idGoogle = $idGoogle;

        return $this;
    }

    /**
     * Get idGoogle
     *
     * @return string
     */
    public function getIdGoogle()
    {
        return $this->idGoogle;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return TacheSynchro
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
     * Set status
     *
     * @param integer $status
     *
     * @return TacheSynchro
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set datefait
     *
     * @param \DateTime $datefait
     *
     * @return TacheSynchro
     */
    public function setDatefait($datefait)
    {
        $this->datefait = $datefait;

        return $this;
    }

    /**
     * Get datefait
     *
     * @return \DateTime
     */
    public function getDatefait()
    {
        return $this->datefait;
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
     * Set tacheEntityLibreAction
     *
     * @param \AppBundle\Entity\TacheEntityLibreAction $tacheEntityLibreAction
     *
     * @return TacheSynchro
     */
    public function setTacheEntityLibreAction(\AppBundle\Entity\TacheEntityLibreAction $tacheEntityLibreAction = null)
    {
        $this->tacheEntityLibreAction = $tacheEntityLibreAction;

        return $this;
    }

    /**
     * Get tacheEntityLibreAction
     *
     * @return \AppBundle\Entity\TacheEntityLibreAction
     */
    public function getTacheEntityLibreAction()
    {
        return $this->tacheEntityLibreAction;
    }

    /**
     * Set tacheEntityLegaleAction
     *
     * @param \AppBundle\Entity\TacheEntityLegaleAction $tacheEntityLegaleAction
     *
     * @return TacheSynchro
     */
    public function setTacheEntityLegaleAction(\AppBundle\Entity\TacheEntityLegaleAction $tacheEntityLegaleAction = null)
    {
        $this->tacheEntityLegaleAction = $tacheEntityLegaleAction;

        return $this;
    }

    /**
     * Get tacheEntityLegaleAction
     *
     * @return \AppBundle\Entity\TacheEntityLegaleAction
     */
    public function getTacheEntityLegaleAction()
    {
        return $this->tacheEntityLegaleAction;
    }

    /**
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return TacheSynchro
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
