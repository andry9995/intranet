<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DownloadGestionAppli
 *
 * @ORM\Table(name="download_gestion_appli")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DownloadGestionAppliRepository")
 */
class DownloadGestionAppli
{
    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="chemin", type="string", length=100, nullable=true)
     */
    private $chemin;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_exe", type="string", length=45, nullable=true)
     */
    private $nomExe;

    /**
     * @var integer
     *
     * @ORM\Column(name="status_appli", type="integer", nullable=true)
     */
    private $statusAppli;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_arret", type="datetime", nullable=true)
     */
    private $dateArret;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set status
     *
     * @param integer $status
     *
     * @return DownloadGestionAppli
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
     * Set chemin
     *
     * @param string $chemin
     *
     * @return DownloadGestionAppli
     */
    public function setChemin($chemin)
    {
        $this->chemin = $chemin;

        return $this;
    }

    /**
     * Get chemin
     *
     * @return string
     */
    public function getChemin()
    {
        return $this->chemin;
    }

    /**
     * Set nomExe
     *
     * @param string $nomExe
     *
     * @return DownloadGestionAppli
     */
    public function setNomExe($nomExe)
    {
        $this->nomExe = $nomExe;

        return $this;
    }

    /**
     * Get nomExe
     *
     * @return string
     */
    public function getNomExe()
    {
        return $this->nomExe;
    }

    /**
     * Set statusAppli
     *
     * @param integer $statusAppli
     *
     * @return DownloadGestionAppli
     */
    public function setStatusAppli($statusAppli)
    {
        $this->statusAppli = $statusAppli;

        return $this;
    }

    /**
     * Get statusAppli
     *
     * @return integer
     */
    public function getStatusAppli()
    {
        return $this->statusAppli;
    }

    /**
     * Set dateArret
     *
     * @param \DateTime $dateArret
     *
     * @return DownloadGestionAppli
     */
    public function setDateArret($dateArret)
    {
        $this->dateArret = $dateArret;

        return $this;
    }

    /**
     * Get dateArret
     *
     * @return \DateTime
     */
    public function getDateArret()
    {
        return $this->dateArret;
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
}
