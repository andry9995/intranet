<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ImputationControleCaisse
 *
 * @ORM\Table(name="imputation_controle_caisse", indexes={@ORM\Index(name="fk_imputation_ctrcaisse_image1_idx", columns={"image_id"}), @ORM\Index(name="fk_imputation_ctrcaisse_caisse_nature1_idx", columns={"entree_caisse_nature_id"}), @ORM\Index(name="fk_imputation_ctrcaisse_caisse_nature2_idx", columns={"sortie_caisse_nature_id"}), @ORM\Index(name="fk_imputation_ctrcaisse_tva_taux1_idx", columns={"entree_tva_taux_id"}), @ORM\Index(name="fk_imputation_ctrcaisse_tva_taux2_idx", columns={"sortie_tva_taux_id"}), @ORM\Index(name="fk_imputation_ctrcaisse_pcc1_idx", columns={"ttc1_pcc_id"}), @ORM\Index(name="fk_imputation_ctrcaisse_journal_dossier1_idx", columns={"journal_dossier_id"}), @ORM\Index(name="fk_imputation_ctrcaisse_code_analytique1_idx", columns={"code_analytique_id"}), @ORM\Index(name="fk_imputation_ctrcaisse_pcc2_idx", columns={"ttc2_pcc_id"}), @ORM\Index(name="fk_imputation_ctrcaisse_pcc3_idx", columns={"ht_pcc_id"}), @ORM\Index(name="fk_imputation_ctrcaisse_pcc4_idx", columns={"tva_pcc_id"}), @ORM\Index(name="fk_imputation_ctrcaisse_mode_reglement1_idx", columns={"mode_reglement_id"}), @ORM\Index(name="fk_imputation_controle_caisse_caisse_type1_idx", columns={"entree_caisse_type_id"}), @ORM\Index(name="fk_imputation_controle_caisse_caisse_type2_idx", columns={"sortie_caisse_type_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ImputationControleCaisseRepository")
 */
class ImputationControleCaisse
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=100, nullable=true)
     */
    private $libelle;

    /**
     * @var float
     *
     * @ORM\Column(name="solde_initial", type="float", precision=10, scale=0, nullable=true)
     */
    private $soldeInitial;

    /**
     * @var float
     *
     * @ORM\Column(name="entree_ttc", type="float", precision=10, scale=0, nullable=true)
     */
    private $entreeTtc;

    /**
     * @var float
     *
     * @ORM\Column(name="entree_tva", type="float", precision=10, scale=0, nullable=true)
     */
    private $entreeTva;

    /**
     * @var float
     *
     * @ORM\Column(name="entree_ht", type="float", precision=10, scale=0, nullable=true)
     */
    private $entreeHt;

    /**
     * @var float
     *
     * @ORM\Column(name="sortie_ttc", type="float", precision=10, scale=0, nullable=true)
     */
    private $sortieTtc;

    /**
     * @var float
     *
     * @ORM\Column(name="sortie_tva", type="float", precision=10, scale=0, nullable=true)
     */
    private $sortieTva;

    /**
     * @var float
     *
     * @ORM\Column(name="sortie_ht", type="float", precision=10, scale=0, nullable=true)
     */
    private $sortieHt;

    /**
     * @var float
     *
     * @ORM\Column(name="solde_final", type="float", precision=10, scale=0, nullable=true)
     */
    private $soldeFinal;

    /**
     * @var integer
     *
     * @ORM\Column(name="row_id", type="integer", nullable=true)
     */
    private $rowId;

    /**
     * @var integer
     *
     * @ORM\Column(name="entree_sortie", type="integer", nullable=true)
     */
    private $entreeSortie;

    /**
     * @var integer
     *
     * @ORM\Column(name="comptabilise", type="integer", nullable=true)
     */
    private $comptabilise;

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
     *   @ORM\JoinColumn(name="ttc2_pcc_id", referencedColumnName="id")
     * })
     */
    private $ttc2Pcc;

    /**
     * @var \AppBundle\Entity\Pcc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ttc1_pcc_id", referencedColumnName="id")
     * })
     */
    private $ttc1Pcc;

    /**
     * @var \AppBundle\Entity\Pcc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ht_pcc_id", referencedColumnName="id")
     * })
     */
    private $htPcc;

    /**
     * @var \AppBundle\Entity\Pcc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tva_pcc_id", referencedColumnName="id")
     * })
     */
    private $tvaPcc;

    /**
     * @var \AppBundle\Entity\TvaTaux
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TvaTaux")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sortie_tva_taux_id", referencedColumnName="id")
     * })
     */
    private $sortieTvaTaux;

    /**
     * @var \AppBundle\Entity\TvaTaux
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TvaTaux")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entree_tva_taux_id", referencedColumnName="id")
     * })
     */
    private $entreeTvaTaux;

    /**
     * @var \AppBundle\Entity\ModeReglement
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ModeReglement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mode_reglement_id", referencedColumnName="id")
     * })
     */
    private $modeReglement;

    /**
     * @var \AppBundle\Entity\JournalDossier
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\JournalDossier")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="journal_dossier_id", referencedColumnName="id")
     * })
     */
    private $journalDossier;

    /**
     * @var \AppBundle\Entity\CaisseNature
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CaisseNature")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entree_caisse_nature_id", referencedColumnName="id")
     * })
     */
    private $entreeCaisseNature;

    /**
     * @var \AppBundle\Entity\CaisseType
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CaisseType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sortie_caisse_type_id", referencedColumnName="id")
     * })
     */
    private $sortieCaisseType;

    /**
     * @var \AppBundle\Entity\CaisseNature
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CaisseNature")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sortie_caisse_nature_id", referencedColumnName="id")
     * })
     */
    private $sortieCaisseNature;

    /**
     * @var \AppBundle\Entity\CodeAnalytique
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CodeAnalytique")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_analytique_id", referencedColumnName="id")
     * })
     */
    private $codeAnalytique;

    /**
     * @var \AppBundle\Entity\Image
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Image")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     * })
     */
    private $image;

    /**
     * @var \AppBundle\Entity\ImageFlague
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ImageFlague")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="image_flague_id", referencedColumnName="id")
     * })
     */
    private $imageFlague;

    /**
     * @var \AppBundle\Entity\CaisseType
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CaisseType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entree_caisse_type_id", referencedColumnName="id")
     * })
     */
    private $entreeCaisseType;



    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return ImputationControleCaisse
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
     * Set libelle
     *
     * @param string $libelle
     *
     * @return ImputationControleCaisse
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set soldeInitial
     *
     * @param float $soldeInitial
     *
     * @return ImputationControleCaisse
     */
    public function setSoldeInitial($soldeInitial)
    {
        $this->soldeInitial = $soldeInitial;

        return $this;
    }

    /**
     * Get soldeInitial
     *
     * @return float
     */
    public function getSoldeInitial()
    {
        return $this->soldeInitial;
    }

    /**
     * Set entreeTtc
     *
     * @param float $entreeTtc
     *
     * @return ImputationControleCaisse
     */
    public function setEntreeTtc($entreeTtc)
    {
        $this->entreeTtc = $entreeTtc;

        return $this;
    }

    /**
     * Get entreeTtc
     *
     * @return float
     */
    public function getEntreeTtc()
    {
        return $this->entreeTtc;
    }

    /**
     * Set entreeTva
     *
     * @param float $entreeTva
     *
     * @return ImputationControleCaisse
     */
    public function setEntreeTva($entreeTva)
    {
        $this->entreeTva = $entreeTva;

        return $this;
    }

    /**
     * Get entreeTva
     *
     * @return float
     */
    public function getEntreeTva()
    {
        return $this->entreeTva;
    }

    /**
     * Set entreeHt
     *
     * @param float $entreeHt
     *
     * @return ImputationControleCaisse
     */
    public function setEntreeHt($entreeHt)
    {
        $this->entreeHt = $entreeHt;

        return $this;
    }

    /**
     * Get entreeHt
     *
     * @return float
     */
    public function getEntreeHt()
    {
        return $this->entreeHt;
    }

    /**
     * Set sortieTtc
     *
     * @param float $sortieTtc
     *
     * @return ImputationControleCaisse
     */
    public function setSortieTtc($sortieTtc)
    {
        $this->sortieTtc = $sortieTtc;

        return $this;
    }

    /**
     * Get sortieTtc
     *
     * @return float
     */
    public function getSortieTtc()
    {
        return $this->sortieTtc;
    }

    /**
     * Set sortieTva
     *
     * @param float $sortieTva
     *
     * @return ImputationControleCaisse
     */
    public function setSortieTva($sortieTva)
    {
        $this->sortieTva = $sortieTva;

        return $this;
    }

    /**
     * Get sortieTva
     *
     * @return float
     */
    public function getSortieTva()
    {
        return $this->sortieTva;
    }

    /**
     * Set sortieHt
     *
     * @param float $sortieHt
     *
     * @return ImputationControleCaisse
     */
    public function setSortieHt($sortieHt)
    {
        $this->sortieHt = $sortieHt;

        return $this;
    }

    /**
     * Get sortieHt
     *
     * @return float
     */
    public function getSortieHt()
    {
        return $this->sortieHt;
    }

    /**
     * Set soldeFinal
     *
     * @param float $soldeFinal
     *
     * @return ImputationControleCaisse
     */
    public function setSoldeFinal($soldeFinal)
    {
        $this->soldeFinal = $soldeFinal;

        return $this;
    }

    /**
     * Get soldeFinal
     *
     * @return float
     */
    public function getSoldeFinal()
    {
        return $this->soldeFinal;
    }

    /**
     * Set rowId
     *
     * @param integer $rowId
     *
     * @return ImputationControleCaisse
     */
    public function setRowId($rowId)
    {
        $this->rowId = $rowId;

        return $this;
    }

    /**
     * Get rowId
     *
     * @return integer
     */
    public function getRowId()
    {
        return $this->rowId;
    }

    /**
     * Set entreeSortie
     *
     * @param integer $entreeSortie
     *
     * @return ImputationControleCaisse
     */
    public function setEntreeSortie($entreeSortie)
    {
        $this->entreeSortie = $entreeSortie;

        return $this;
    }

    /**
     * Get entreeSortie
     *
     * @return integer
     */
    public function getEntreeSortie()
    {
        return $this->entreeSortie;
    }

    /**
     * Set comptabilise
     *
     * @param integer $comptabilise
     *
     * @return ImputationControleCaisse
     */
    public function setComptabilise($comptabilise)
    {
        $this->comptabilise = $comptabilise;

        return $this;
    }

    /**
     * Get comptabilise
     *
     * @return integer
     */
    public function getComptabilise()
    {
        return $this->comptabilise;
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
     * Set ttc2Pcc
     *
     * @param \AppBundle\Entity\Pcc $ttc2Pcc
     *
     * @return ImputationControleCaisse
     */
    public function setTtc2Pcc(\AppBundle\Entity\Pcc $ttc2Pcc = null)
    {
        $this->ttc2Pcc = $ttc2Pcc;

        return $this;
    }

    /**
     * Get ttc2Pcc
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getTtc2Pcc()
    {
        return $this->ttc2Pcc;
    }

    /**
     * Set ttc1Pcc
     *
     * @param \AppBundle\Entity\Pcc $ttc1Pcc
     *
     * @return ImputationControleCaisse
     */
    public function setTtc1Pcc(\AppBundle\Entity\Pcc $ttc1Pcc = null)
    {
        $this->ttc1Pcc = $ttc1Pcc;

        return $this;
    }

    /**
     * Get ttc1Pcc
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getTtc1Pcc()
    {
        return $this->ttc1Pcc;
    }

    /**
     * Set htPcc
     *
     * @param \AppBundle\Entity\Pcc $htPcc
     *
     * @return ImputationControleCaisse
     */
    public function setHtPcc(\AppBundle\Entity\Pcc $htPcc = null)
    {
        $this->htPcc = $htPcc;

        return $this;
    }

    /**
     * Get htPcc
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getHtPcc()
    {
        return $this->htPcc;
    }

    /**
     * Set tvaPcc
     *
     * @param \AppBundle\Entity\Pcc $tvaPcc
     *
     * @return ImputationControleCaisse
     */
    public function setTvaPcc(\AppBundle\Entity\Pcc $tvaPcc = null)
    {
        $this->tvaPcc = $tvaPcc;

        return $this;
    }

    /**
     * Get tvaPcc
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getTvaPcc()
    {
        return $this->tvaPcc;
    }

    /**
     * Set sortieTvaTaux
     *
     * @param \AppBundle\Entity\TvaTaux $sortieTvaTaux
     *
     * @return ImputationControleCaisse
     */
    public function setSortieTvaTaux(\AppBundle\Entity\TvaTaux $sortieTvaTaux = null)
    {
        $this->sortieTvaTaux = $sortieTvaTaux;

        return $this;
    }

    /**
     * Get sortieTvaTaux
     *
     * @return \AppBundle\Entity\TvaTaux
     */
    public function getSortieTvaTaux()
    {
        return $this->sortieTvaTaux;
    }

    /**
     * Set entreeTvaTaux
     *
     * @param \AppBundle\Entity\TvaTaux $entreeTvaTaux
     *
     * @return ImputationControleCaisse
     */
    public function setEntreeTvaTaux(\AppBundle\Entity\TvaTaux $entreeTvaTaux = null)
    {
        $this->entreeTvaTaux = $entreeTvaTaux;

        return $this;
    }

    /**
     * Get entreeTvaTaux
     *
     * @return \AppBundle\Entity\TvaTaux
     */
    public function getEntreeTvaTaux()
    {
        return $this->entreeTvaTaux;
    }

    /**
     * Set modeReglement
     *
     * @param \AppBundle\Entity\ModeReglement $modeReglement
     *
     * @return ImputationControleCaisse
     */
    public function setModeReglement(\AppBundle\Entity\ModeReglement $modeReglement = null)
    {
        $this->modeReglement = $modeReglement;

        return $this;
    }

    /**
     * Get modeReglement
     *
     * @return \AppBundle\Entity\ModeReglement
     */
    public function getModeReglement()
    {
        return $this->modeReglement;
    }

    /**
     * Set journalDossier
     *
     * @param \AppBundle\Entity\JournalDossier $journalDossier
     *
     * @return ImputationControleCaisse
     */
    public function setJournalDossier(\AppBundle\Entity\JournalDossier $journalDossier = null)
    {
        $this->journalDossier = $journalDossier;

        return $this;
    }

    /**
     * Get journalDossier
     *
     * @return \AppBundle\Entity\JournalDossier
     */
    public function getJournalDossier()
    {
        return $this->journalDossier;
    }

    /**
     * Set entreeCaisseNature
     *
     * @param \AppBundle\Entity\CaisseNature $entreeCaisseNature
     *
     * @return ImputationControleCaisse
     */
    public function setEntreeCaisseNature(\AppBundle\Entity\CaisseNature $entreeCaisseNature = null)
    {
        $this->entreeCaisseNature = $entreeCaisseNature;

        return $this;
    }

    /**
     * Get entreeCaisseNature
     *
     * @return \AppBundle\Entity\CaisseNature
     */
    public function getEntreeCaisseNature()
    {
        return $this->entreeCaisseNature;
    }

    /**
     * Set sortieCaisseType
     *
     * @param \AppBundle\Entity\CaisseType $sortieCaisseType
     *
     * @return ImputationControleCaisse
     */
    public function setSortieCaisseType(\AppBundle\Entity\CaisseType $sortieCaisseType = null)
    {
        $this->sortieCaisseType = $sortieCaisseType;

        return $this;
    }

    /**
     * Get sortieCaisseType
     *
     * @return \AppBundle\Entity\CaisseType
     */
    public function getSortieCaisseType()
    {
        return $this->sortieCaisseType;
    }

    /**
     * Set sortieCaisseNature
     *
     * @param \AppBundle\Entity\CaisseNature $sortieCaisseNature
     *
     * @return ImputationControleCaisse
     */
    public function setSortieCaisseNature(\AppBundle\Entity\CaisseNature $sortieCaisseNature = null)
    {
        $this->sortieCaisseNature = $sortieCaisseNature;

        return $this;
    }

    /**
     * Get sortieCaisseNature
     *
     * @return \AppBundle\Entity\CaisseNature
     */
    public function getSortieCaisseNature()
    {
        return $this->sortieCaisseNature;
    }

    /**
     * Set codeAnalytique
     *
     * @param \AppBundle\Entity\CodeAnalytique $codeAnalytique
     *
     * @return ImputationControleCaisse
     */
    public function setCodeAnalytique(\AppBundle\Entity\CodeAnalytique $codeAnalytique = null)
    {
        $this->codeAnalytique = $codeAnalytique;

        return $this;
    }

    /**
     * Get codeAnalytique
     *
     * @return \AppBundle\Entity\CodeAnalytique
     */
    public function getCodeAnalytique()
    {
        return $this->codeAnalytique;
    }

    /**
     * Set image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return ImputationControleCaisse
     */
    public function setImage(\AppBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \AppBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }


    /**
     * Set imageFlague
     *
     * @param \AppBundle\Entity\ImageFlague $imageFlague
     *
     * @return $this
     */
    public function setImageFlague(\AppBundle\Entity\ImageFlague $imageFlague = null)
    {
        $this->imageFlague = $imageFlague;

        return $this;
    }

    /**
     * Get imageFlague
     *
     * @return \AppBundle\Entity\ImageFlague
     */
    public function getImageFlague()
    {
        return $this->imageFlague;
    }

    /**
     * Set entreeCaisseType
     *
     * @param \AppBundle\Entity\CaisseType $entreeCaisseType
     *
     * @return ImputationControleCaisse
     */
    public function setEntreeCaisseType(\AppBundle\Entity\CaisseType $entreeCaisseType = null)
    {
        $this->entreeCaisseType = $entreeCaisseType;

        return $this;
    }

    /**
     * Get entreeCaisseType
     *
     * @return \AppBundle\Entity\CaisseType
     */
    public function getEntreeCaisseType()
    {
        return $this->entreeCaisseType;
    }
}
