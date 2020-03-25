<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TachePrioriteDossier
 *
 * @ORM\Table(name="tache_priorite_dossier", uniqueConstraints={@ORM\UniqueConstraint(name="dossier_id_UNIQUE", columns={"dossier_id"})}, indexes={@ORM\Index(name="fk_tache_priorite_dossier_tache_synchro_idx", columns={"tache_synchro_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TachePrioriteDossierRepository")
 */
class TachePrioriteDossier
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_calcul", type="date", nullable=false)
     */
    private $dateCalcul;

    /**
     * @var integer
     *
     * @ORM\Column(name="priorite_manuel", type="integer", nullable=false)
     */
    private $prioriteManuel = '100';

    /**
     * @var string
     *
     * @ORM\Column(name="google_id", type="string", length=45, nullable=true)
     */
    private $googleId;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * @var \AppBundle\Entity\TacheSynchro
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TacheSynchro")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_synchro_id", referencedColumnName="id")
     * })
     */
    private $tacheSynchro;



    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return TachePrioriteDossier
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
     * Set dateCalcul
     *
     * @param \DateTime $dateCalcul
     *
     * @return TachePrioriteDossier
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
     * Set prioriteManuel
     *
     * @param integer $prioriteManuel
     *
     * @return TachePrioriteDossier
     */
    public function setPrioriteManuel($prioriteManuel)
    {
        $this->prioriteManuel = $prioriteManuel;

        return $this;
    }

    /**
     * Get prioriteManuel
     *
     * @return integer
     */
    public function getPrioriteManuel()
    {
        return $this->prioriteManuel;
    }

    /**
     * Set googleId
     *
     * @param string $googleId
     *
     * @return TachePrioriteDossier
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;

        return $this;
    }

    /**
     * Get googleId
     *
     * @return string
     */
    public function getGoogleId()
    {
        return $this->googleId;
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
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return TachePrioriteDossier
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
     * Set tacheSynchro
     *
     * @param \AppBundle\Entity\TacheSynchro $tacheSynchro
     *
     * @return TachePrioriteDossier
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
}
