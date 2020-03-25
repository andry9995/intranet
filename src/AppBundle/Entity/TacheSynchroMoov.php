<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TacheSynchroMoov
 *
 * @ORM\Table(name="tache_synchro_moov", indexes={@ORM\Index(name="fk_tache_synchro_moov_tache_synchro_idx", columns={"tache_synchro_id"}), @ORM\Index(name="fk_tache_synchro_moov_operateur_idx", columns={"operateur_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TacheSynchroMoovRepository")
 */
class TacheSynchroMoov
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\TacheSynchro
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TacheSynchro")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_synchro_id", referencedColumnName="id")
     * })
     */
    private $tacheSynchro;

    /**
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="operateur_id", referencedColumnName="id")
     * })
     */
    private $operateur;



    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return TacheSynchroMoov
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tacheSynchro
     *
     * @param \AppBundle\Entity\TacheSynchro $tacheSynchro
     *
     * @return TacheSynchroMoov
     */
    public function setTacheSynchro(\AppBundle\Entity\TacheSynchro $tacheSynchro = null)
    {
        $this->tacheSynchro = $tacheSynchro;

        return $this;
    }

    /**
     * Get tacheSynchro
     *
     * @return \AppBundle\Entity\TacheSynchro
     */
    public function getTacheSynchro()
    {
        return $this->tacheSynchro;
    }

    /**
     * Set operateur
     *
     * @param \AppBundle\Entity\Operateur $operateur
     *
     * @return TacheSynchroMoov
     */
    public function setOperateur(\AppBundle\Entity\Operateur $operateur = null)
    {
        $this->operateur = $operateur;

        return $this;
    }

    /**
     * Get operateur
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getOperateur()
    {
        return $this->operateur;
    }
}
