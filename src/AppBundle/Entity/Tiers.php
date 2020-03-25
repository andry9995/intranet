<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tiers
 *
 * @ORM\Table(name="tiers", indexes={@ORM\Index(name="fk_tiers_dossier1_idx", columns={"dossier_id"}), @ORM\Index(name="fk_tiers_historique_upload1_idx", columns={"historique_upload_id"}), @ORM\Index(name="fk_tiers_pcc1_idx", columns={"pcc_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TiersRepository")
 */
class Tiers
{
    /**
     * @var binary
     *
     * @ORM\Column(name="compte", type="binary", nullable=false)
     */
    private $compte = '';

    /**
     * @var string
     *
     * @ORM\Column(name="intitule", type="string", length=150, nullable=false)
     */
    private $intitule = '';

    /**
     * @var string
     *
     * @ORM\Column(name="abrev", type="string", length=20, nullable=true)
     */
    private $abrev = '';

    /**
     * @var string
     *
     * @ORM\Column(name="siret", type="string", length=20, nullable=true)
     */
    private $siret = '';

    /**
     * @var string
     *
     * @ORM\Column(name="siren", type="string", length=20, nullable=true)
     */
    private $siren = '';

    /**
     * @var string
     *
     * @ORM\Column(name="rcs", type="string", length=50, nullable=true)
     */
    private $rcs = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type = '3';

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="compte_str", type="string", length=100, nullable=false)
     */
    private $compteStr;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Pcc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pcc_id", referencedColumnName="id")
     * })
     */
    private $pcc;

    /**
     * @var \AppBundle\Entity\HistoriqueUpload
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\HistoriqueUpload")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="historique_upload_id", referencedColumnName="id")
     * })
     */
    private $historiqueUpload;

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
     * Set compte
     *
     * @param binary $compte
     *
     * @return Tiers
     */
    public function setCompte($compte)
    {
        $this->compte = $compte;

        return $this;
    }

    /**
     * Get compte
     *
     * @return binary
     */
    public function getCompte()
    {
        return $this->compte;
    }

    /**
     * Set intitule
     *
     * @param string $intitule
     *
     * @return Tiers
     */
    public function setIntitule($intitule)
    {
        $this->intitule = $intitule;

        return $this;
    }

    /**
     * Get intitule
     *
     * @return string
     */
    public function getIntitule()
    {
        return $this->intitule;
    }

    /**
     * Set abrev
     *
     * @param string $abrev
     *
     * @return Tiers
     */
    public function setAbrev($abrev)
    {
        $this->abrev = $abrev;

        return $this;
    }

    /**
     * Get abrev
     *
     * @return string
     */
    public function getAbrev()
    {
        return $this->abrev;
    }

    /**
     * Set siret
     *
     * @param string $siret
     *
     * @return Tiers
     */
    public function setSiret($siret)
    {
        $this->siret = $siret;

        return $this;
    }

    /**
     * Get siret
     *
     * @return string
     */
    public function getSiret()
    {
        return $this->siret;
    }

    /**
     * Set siren
     *
     * @param string $siren
     *
     * @return Tiers
     */
    public function setSiren($siren)
    {
        $this->siren = $siren;

        return $this;
    }

    /**
     * Get siren
     *
     * @return string
     */
    public function getSiren()
    {
        return $this->siren;
    }

    /**
     * Set rcs
     *
     * @param string $rcs
     *
     * @return Tiers
     */
    public function setRcs($rcs)
    {
        $this->rcs = $rcs;

        return $this;
    }

    /**
     * Get rcs
     *
     * @return string
     */
    public function getRcs()
    {
        return $this->rcs;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Tiers
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Tiers
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
     * Set compteStr
     *
     * @param string $compteStr
     *
     * @return Tiers
     */
    public function setCompteStr($compteStr)
    {
        $this->compteStr = $compteStr;

        return $this;
    }

    /**
     * Get compteStr
     *
     * @return string
     */
    public function getCompteStr()
    {
        return $this->compteStr;
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
     * Set pcc
     *
     * @param \AppBundle\Entity\Pcc $pcc
     *
     * @return Tiers
     */
    public function setPcc(\AppBundle\Entity\Pcc $pcc = null)
    {
        $this->pcc = $pcc;

        return $this;
    }

    /**
     * Get pcc
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getPcc()
    {
        return $this->pcc;
    }

    /**
     * Set historiqueUpload
     *
     * @param \AppBundle\Entity\HistoriqueUpload $historiqueUpload
     *
     * @return Tiers
     */
    public function setHistoriqueUpload(\AppBundle\Entity\HistoriqueUpload $historiqueUpload = null)
    {
        $this->historiqueUpload = $historiqueUpload;

        return $this;
    }

    /**
     * Get historiqueUpload
     *
     * @return \AppBundle\Entity\HistoriqueUpload
     */
    public function getHistoriqueUpload()
    {
        return $this->historiqueUpload;
    }

    /**
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return Tiers
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
