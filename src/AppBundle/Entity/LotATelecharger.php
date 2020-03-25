<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LotATelecharger
 *
 * @ORM\Table(name="lot_a_telecharger", indexes={@ORM\Index(name="fk_lotatele_lot_idx", columns={"lot_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LotATelechargerRepository")
 */
class LotATelecharger
{
    /**
     * @var string
     *
     * @ORM\Column(name="cabinet", type="string", length=100, nullable=true)
     */
    private $cabinet;

    /**
     * @var string
     *
     * @ORM\Column(name="dossier", type="string", length=100, nullable=true)
     */
    private $dossier;

    /**
     * @var string
     *
     * @ORM\Column(name="exercice", type="string", length=4, nullable=true)
     */
    private $exercice;

    /**
     * @var string
     *
     * @ORM\Column(name="date_scan", type="date", nullable=false)
     */
    private $dateScan;

    /**
     * @var integer
     *
     * @ORM\Column(name="lot", type="integer", nullable=true)
     */
    private $lot;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_image", type="integer", nullable=false)
     */
    private $nbImage = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
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
     * @var \AppBundle\Entity\Lot
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Lot")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     * })
     */
    private $lot2;



    /**
     * Set cabinet
     *
     * @param string $cabinet
     *
     * @return LotATelecharger
     */
    public function setCabinet($cabinet)
    {
        $this->cabinet = $cabinet;

        return $this;
    }

    /**
     * Get cabinet
     *
     * @return string
     */
    public function getCabinet()
    {
        return $this->cabinet;
    }

    /**
     * Set dossier
     *
     * @param string $dossier
     *
     * @return LotATelecharger
     */
    public function setDossier($dossier)
    {
        $this->dossier = $dossier;

        return $this;
    }

    /**
     * Get dossier
     *
     * @return string
     */
    public function getDossier()
    {
        return $this->dossier;
    }

    /**
     * Set exercice
     *
     * @param string $exercice
     *
     * @return LotATelecharger
     */
    public function setExercice($exercice)
    {
        $this->exercice = $exercice;

        return $this;
    }

    /**
     * Get exercice
     *
     * @return string
     */
    public function getExercice()
    {
        return $this->exercice;
    }

    /**
     * Set dateScan
     *
     * @param string $dateScan
     *
     * @return LotATelecharger
     */
    public function setDateScan($dateScan)
    {
        $this->dateScan = $dateScan;

        return $this;
    }

    /**
     * Get dateScan
     *
     * @return string
     */
    public function getDateScan()
    {
        return $this->dateScan;
    }

    /**
     * Set lot
     *
     * @param integer $lot
     *
     * @return LotATelecharger
     */
    public function setLot($lot)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * Get lot
     *
     * @return integer
     */
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * @param $nbImage
     * @return $this
     */
    public function setNbImage($nbImage)
    {
        $this->nbImage = $nbImage;

        return $this;
    }

    /**
     * @return int
     */
    public function getNbImage()
    {
        return $this->nbImage;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return LotATelecharger
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
     * Set lot2
     *
     * @param \AppBundle\Entity\Lot $lot2
     *
     * @return LotATelecharger
     */
    public function setLot2(\AppBundle\Entity\Lot $lot2 = null)
    {
        $this->lot2 = $lot2;

        return $this;
    }

    /**
     * Get lot2
     *
     * @return \AppBundle\Entity\Lot
     */
    public function getLot2()
    {
        return $this->lot2;
    }


}
