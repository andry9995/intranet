<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ImageFtp
 *
 * @ORM\Table(name="image_ftp", indexes={@ORM\Index(name="fk_image_ftp_client_idx", columns={"client_id"}), @ORM\Index(name="fk_image_ftp_dossier_idx", columns={"dossier_id"})})
 * @ORM\Entity
 */
class ImageFtp
{
    /**
     * @var integer
     *
     * @ORM\Column(name="exercice", type="integer", nullable=true)
     */
    private $exercice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datescan", type="date", nullable=false)
     */
    private $datescan;

    /**
     * @var string
     *
     * @ORM\Column(name="original", type="string", length=255, nullable=false)
     */
    private $original;

    /**
     * @var string
     *
     * @ORM\Column(name="path_ftp", type="text", length=65535, nullable=false)
     */
    private $pathFtp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_traitement", type="date", nullable=true)
     */
    private $dateTraitement;

    /**
     * @var integer
     *
     * @ORM\Column(name="cloture", type="integer", nullable=true)
     */
    private $cloture;

    /**
     * @var boolean
     *
     * @ORM\Column(name="autre", type="boolean", nullable=true)
     */
    private $autre = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
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
     * Set exercice
     *
     * @param integer $exercice
     *
     * @return ImageFtp
     */
    public function setExercice($exercice)
    {
        $this->exercice = $exercice;

        return $this;
    }

    /**
     * Get exercice
     *
     * @return integer
     */
    public function getExercice()
    {
        return $this->exercice;
    }

    /**
     * Set datescan
     *
     * @param \DateTime $datescan
     *
     * @return ImageFtp
     */
    public function setDatescan($datescan)
    {
        $this->datescan = $datescan;

        return $this;
    }

    /**
     * Get datescan
     *
     * @return \DateTime
     */
    public function getDatescan()
    {
        return $this->datescan;
    }

    /**
     * Set original
     *
     * @param string $original
     *
     * @return ImageFtp
     */
    public function setOriginal($original)
    {
        $this->original = $original;

        return $this;
    }

    /**
     * Get original
     *
     * @return string
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Set pathFtp
     *
     * @param string $pathFtp
     *
     * @return ImageFtp
     */
    public function setPathFtp($pathFtp)
    {
        $this->pathFtp = $pathFtp;

        return $this;
    }

    /**
     * Get pathFtp
     *
     * @return string
     */
    public function getPathFtp()
    {
        return $this->pathFtp;
    }

    /**
     * Set dateTraitement
     *
     * @param \DateTime $dateTraitement
     *
     * @return ImageFtp
     */
    public function setDateTraitement($dateTraitement)
    {
        $this->dateTraitement = $dateTraitement;

        return $this;
    }

    /**
     * Get dateTraitement
     *
     * @return \DateTime
     */
    public function getDateTraitement()
    {
        return $this->dateTraitement;
    }

    /**
     * Set cloture
     *
     * @param integer $cloture
     *
     * @return ImageFtp
     */
    public function setCloture($cloture)
    {
        $this->cloture = $cloture;

        return $this;
    }

    /**
     * Get cloture
     *
     * @return integer
     */
    public function getCloture()
    {
        return $this->cloture;
    }

    /**
     * Set autre
     *
     * @param boolean $autre
     *
     * @return ImageFtp
     */
    public function setAutre($autre)
    {
        $this->autre = $autre;

        return $this;
    }

    /**
     * Get autre
     *
     * @return boolean
     */
    public function getAutre()
    {
        return $this->autre;
    }

    /**
     * Set status
     *
     * @param boolean $status
     *
     * @return ImageFtp
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
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
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return ImageFtp
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
     * @return ImageFtp
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
