<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TacheEntityLegaleAction
 *
 * @ORM\Table(name="tache_entity_legale_action", uniqueConstraints={@ORM\UniqueConstraint(name="unik_tache_entity_tache_legale_action", columns={"tache_entity_id", "tache_legale_action_id"})}, indexes={@ORM\Index(name="fk_tache_entity_legale_action_tache_legale_action_idx", columns={"tache_legale_action_id"}), @ORM\Index(name="fk_tache_entity_legale_action_tache_entity_idx", columns={"tache_entity_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TacheEntityLegaleActionRepository")
 */
class TacheEntityLegaleAction
{
    /**
     * @var integer
     *
     * @ORM\Column(name="jour_additif", type="integer", nullable=false)
     */
    private $jourAdditif = '0';

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
     * @var \AppBundle\Entity\TacheLegaleAction
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TacheLegaleAction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_legale_action_id", referencedColumnName="id")
     * })
     */
    private $tacheLegaleAction;

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
     * Set jourAdditif
     *
     * @param integer $jourAdditif
     *
     * @return TacheEntityLegaleAction
     */
    public function setJourAdditif($jourAdditif)
    {
        $this->jourAdditif = $jourAdditif;

        return $this;
    }

    /**
     * Get jourAdditif
     *
     * @return integer
     */
    public function getJourAdditif()
    {
        return $this->jourAdditif;
    }

    /**
     * Set responsable
     *
     * @param integer $responsable
     *
     * @return TacheEntityLegaleAction
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
     * Set tacheLegaleAction
     *
     * @param \AppBundle\Entity\TacheLegaleAction $tacheLegaleAction
     *
     * @return TacheEntityLegaleAction
     */
    public function setTacheLegaleAction(\AppBundle\Entity\TacheLegaleAction $tacheLegaleAction = null)
    {
        $this->tacheLegaleAction = $tacheLegaleAction;

        return $this;
    }

    /**
     * Get tacheLegaleAction
     *
     * @return \AppBundle\Entity\TacheLegaleAction
     */
    public function getTacheLegaleAction()
    {
        return $this->tacheLegaleAction;
    }

    /**
     * Set tacheEntity
     *
     * @param \AppBundle\Entity\TacheEntity $tacheEntity
     *
     * @return TacheEntityLegaleAction
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
