<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TacheStatus
 *
 * @ORM\Table(name="tache_status", indexes={@ORM\Index(name="fk_tache_status_dossier1_idx", columns={"dossier_id"}), @ORM\Index(name="fk_tache_status_tache_dossier1_idx", columns={"tache_dossier_id"}), @ORM\Index(name="fk_tache_status_tache_legale1_idx", columns={"tache_legale_id"}), @ORM\Index(name="fk_tache_status_tache_legale_action1_idx", columns={"tache_legale_action_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TacheStatusRepository")
 */
class TacheStatus
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
     * @ORM\Column(name="report_date", type="date", nullable=true)
     */
    private $reportDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = '0';

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
     * @var \AppBundle\Entity\TacheLegale
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TacheLegale")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_legale_id", referencedColumnName="id")
     * })
     */
    private $tacheLegale;

    /**
     * @var \AppBundle\Entity\TacheDossier
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TacheDossier")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_dossier_id", referencedColumnName="id")
     * })
     */
    private $tacheDossier;

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
     * @return TacheStatus
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
     * Set reportDate
     *
     * @param \DateTime $reportDate
     *
     * @return TacheStatus
     */
    public function setReportDate($reportDate)
    {
        $this->reportDate = $reportDate;

        return $this;
    }

    /**
     * Get reportDate
     *
     * @return \DateTime
     */
    public function getReportDate()
    {
        return $this->reportDate;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return TacheStatus
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
     * @return TacheStatus
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
     * Set tacheLegale
     *
     * @param \AppBundle\Entity\TacheLegale $tacheLegale
     *
     * @return TacheStatus
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
     * Set tacheDossier
     *
     * @param \AppBundle\Entity\TacheDossier $tacheDossier
     *
     * @return TacheStatus
     */
    public function setTacheDossier(\AppBundle\Entity\TacheDossier $tacheDossier = null)
    {
        $this->tacheDossier = $tacheDossier;

        return $this;
    }

    /**
     * Get tacheDossier
     *
     * @return \AppBundle\Entity\TacheDossier
     */
    public function getTacheDossier()
    {
        return $this->tacheDossier;
    }

    /**
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return TacheStatus
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
