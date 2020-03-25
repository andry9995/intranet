<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pcc
 *
 * @ORM\Table(name="pcc", indexes={@ORM\Index(name="fk_pcg_dossier_dossier1_idx", columns={"dossier_id"}), @ORM\Index(name="fk_pcc_historique_upload1_idx", columns={"historique_upload_id"}), @ORM\Index(name="index_compte_dossier", columns={"compte", "dossier_id"}), @ORM\Index(name="index_pcc_compte", columns={"compte"}), @ORM\Index(name="fk_pcc_operateur_id_idx", columns={"operateur_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PccRepository")
 */
class Pcc
{
    /**
     * @var string
     *
     * @ORM\Column(name="compte", type="string", length=20, nullable=false)
     */
    private $compte = '';

    /**
     * @var string
     *
     * @ORM\Column(name="intitule", type="string", length=150, nullable=false)
     */
    private $intitule = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="collectif_tiers", type="integer", nullable=false)
     */
    private $collectifTiers = '-1';

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
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="operateur_id", referencedColumnName="id")
     * })
     */
    private $operateur;

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
     * Set compte
     *
     * @param string $compte
     *
     * @return Pcc
     */
    public function setCompte($compte)
    {
        $this->compte = $compte;

        return $this;
    }

    /**
     * Get compte
     *
     * @return string
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
     * @return Pcc
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
     * Set status
     *
     * @param integer $status
     *
     * @return Pcc
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
     * Set collectifTiers
     *
     * @param integer $collectifTiers
     *
     * @return Pcc
     */
    public function setCollectifTiers($collectifTiers)
    {
        $this->collectifTiers = $collectifTiers;

        return $this;
    }

    /**
     * Get collectifTiers
     *
     * @return integer
     */
    public function getCollectifTiers()
    {
        return $this->collectifTiers;
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
     * @return Pcc
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
     * Set operateur
     *
     * @param \AppBundle\Entity\Operateur $operateur
     *
     * @return Pcc
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

    /**
     * Set historiqueUpload
     *
     * @param \AppBundle\Entity\HistoriqueUpload $historiqueUpload
     *
     * @return Pcc
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
}
