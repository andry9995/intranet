<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TachesPrioriteDossier
 *
 * @ORM\Table(name="taches_priorite_dossier", indexes={@ORM\Index(name="fk_taches_priorite_dossier_taches_synchro_idx", columns={"taches_synchro_id"}), @ORM\Index(name="fk_taches_priorite_dossier_dossier_idx", columns={"dossier_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TachesPrioriteDossierRepository")
 */
class TachesPrioriteDossier
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
     * @var \AppBundle\Entity\TachesSynchro
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TachesSynchro")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="taches_synchro_id", referencedColumnName="id")
     * })
     */
    private $tachesSynchro;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return TachesPrioriteDossier
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
     * @return TachesPrioriteDossier
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
     * @return TachesPrioriteDossier
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
     * @return TachesPrioriteDossier
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
     * Set tachesSynchro
     *
     * @param \AppBundle\Entity\TachesSynchro $tachesSynchro
     *
     * @return TachesPrioriteDossier
     */
    public function setTachesSynchro(\AppBundle\Entity\TachesSynchro $tachesSynchro = null)
    {
        $this->tachesSynchro = $tachesSynchro;

        return $this;
    }

    /**
     * Get tachesSynchro
     *
     * @return \AppBundle\Entity\TachesSynchro
     */
    public function getTachesSynchro()
    {
        return $this->tachesSynchro;
    }

    /**
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return TachesPrioriteDossier
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
