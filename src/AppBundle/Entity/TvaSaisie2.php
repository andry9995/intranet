<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TvaSaisie2
 *
 * @ORM\Table(name="tva_saisie2", indexes={@ORM\Index(name="fk_image_id21_idx", columns={"image_id"}), @ORM\Index(name="fk_tva_saisie2_type_vente1_idx", columns={"type_vente_id"}), @ORM\Index(name="fk_tva_saisie2_tva_taux1_idx", columns={"tva_taux_id"}), @ORM\Index(name="fk_tva_saisie2_sousnature1_idx", columns={"sousnature_id"}), @ORM\Index(name="fk_tva_saisie2_soussouscategorie1_idx", columns={"soussouscategorie_id"}), @ORM\Index(name="fk_tva_saisie2_natureid_idx", columns={"nature_id"}), @ORM\Index(name="fk_tva_saisie2_pcc1_idx", columns={"pcc_id"}), @ORM\Index(name="fk_tva_saisie2_tiers1_idx", columns={"tiers_id"}), @ORM\Index(name="fk_tva_saisie2_pccbilan_idx", columns={"pcc_bilan_id"}), @ORM\Index(name="fk_tva_saisie2_pcctva_idx", columns={"pcc_tva_id"}), @ORM\Index(name="fk_tva_saisie2_caisse_nature1_idx", columns={"caisse_nature_id"}), @ORM\Index(name="fk_tva_saisie2_caisse_type1_idx", columns={"caisse_type_id"}), @ORM\Index(name="fk_tva_saisie2_code_analytique1_idx", columns={"code_analytique_id"}), @ORM\Index(name="fk_tva_saisie2_mode_reglement1_idx", columns={"mode_reglement_id"}), @ORM\Index(name="fk_tva_saisie2_journal_dossier1_idx", columns={"journal_dossier_id"}), @ORM\Index(name="fk_tva_saisie2_pays1_idx", columns={"pays_id"}), @ORM\Index(name="fk_tva_saisie2_devise1_idx", columns={"devise_id"}), @ORM\Index(name="fk_tva_saisie2_condition_depense1_idx", columns={"condition_depense_id"}), @ORM\Index(name="fk_tva_saisie2_vehicule1_idx", columns={"vehicule_id"})})
 * @ORM\Entity
 */
class TvaSaisie2
{
    /**
     * @var float
     *
     * @ORM\Column(name="montant_ht", type="float", precision=10, scale=0, nullable=true)
     */
    private $montantHt;

    /**
     * @var float
     *
     * @ORM\Column(name="taux_tva", type="float", precision=10, scale=0, nullable=true)
     */
    private $tauxTva;

    /**
     * @var string
     *
     * @ORM\Column(name="nature", type="string", length=50, nullable=true)
     */
    private $nature;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="periode_deb", type="date", nullable=true)
     */
    private $periodeDeb;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="periode_fin", type="date", nullable=true)
     */
    private $periodeFin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_livraison", type="date", nullable=true)
     */
    private $dateLivraison;

    /**
     * @var string
     *
     * @ORM\Column(name="prelibelle", type="string", length=100, nullable=true)
     */
    private $prelibelle;

    /**
     * @var float
     *
     * @ORM\Column(name="montant_ttc", type="float", precision=10, scale=0, nullable=false)
     */
    private $montantTtc = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="solde_initial", type="float", precision=10, scale=0, nullable=true)
     */
    private $soldeInitial;

    /**
     * @var integer
     *
     * @ORM\Column(name="entree_sortie", type="integer", nullable=true)
     */
    private $entreeSortie;

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
     * @ORM\Column(name="engagement_tresorerie", type="integer", nullable=true)
     */
    private $engagementTresorerie;

    /**
     * @var float
     *
     * @ORM\Column(name="montant_ttc_devise", type="float", precision=10, scale=0, nullable=true)
     */
    private $montantTtcDevise;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=45, nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbre_couvert", type="integer", nullable=true)
     */
    private $nbreCouvert;

    /**
     * @var float
     *
     * @ORM\Column(name="distance", type="float", precision=10, scale=0, nullable=true)
     */
    private $distance;

    /**
     * @var integer
     *
     * @ORM\Column(name="groupe", type="integer", nullable=true)
     */
    private $groupe;

    /**
     * @var integer
     *
     * @ORM\Column(name="trajet", type="integer", nullable=true)
     */
    private $trajet;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Sousnature
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Sousnature")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sousnature_id", referencedColumnName="id")
     * })
     */
    private $sousnature;

    /**
     * @var \AppBundle\Entity\Pcc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pcc_tva_id", referencedColumnName="id")
     * })
     */
    private $pccTva;

    /**
     * @var \AppBundle\Entity\Pcc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pcc_bilan_id", referencedColumnName="id")
     * })
     */
    private $pccBilan;

    /**
     * @var \AppBundle\Entity\Soussouscategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Soussouscategorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="soussouscategorie_id", referencedColumnName="id")
     * })
     */
    private $soussouscategorie;

    /**
     * @var \AppBundle\Entity\Tiers
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Tiers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tiers_id", referencedColumnName="id")
     * })
     */
    private $tiers;

    /**
     * @var \AppBundle\Entity\Vehicule
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Vehicule")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicule_id", referencedColumnName="id")
     * })
     */
    private $vehicule;

    /**
     * @var \AppBundle\Entity\TypeVente
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TypeVente")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_vente_id", referencedColumnName="id")
     * })
     */
    private $typeVente;

    /**
     * @var \AppBundle\Entity\TvaTaux
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TvaTaux")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tva_taux_id", referencedColumnName="id")
     * })
     */
    private $tvaTaux;

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
     * @var \AppBundle\Entity\Pays
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pays")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pays_id", referencedColumnName="id")
     * })
     */
    private $pays;

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
     * @var \AppBundle\Entity\CaisseType
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CaisseType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="caisse_type_id", referencedColumnName="id")
     * })
     */
    private $caisseType;

    /**
     * @var \AppBundle\Entity\CaisseNature
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CaisseNature")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="caisse_nature_id", referencedColumnName="id")
     * })
     */
    private $caisseNature;

    /**
     * @var \AppBundle\Entity\ConditionDepense
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ConditionDepense")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="condition_depense_id", referencedColumnName="id")
     * })
     */
    private $conditionDepense;

    /**
     * @var \AppBundle\Entity\Devise
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Devise")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="devise_id", referencedColumnName="id")
     * })
     */
    private $devise;

    /**
     * @var \AppBundle\Entity\Nature
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Nature")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="nature_id", referencedColumnName="id")
     * })
     */
    private $nature2;

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
     * @var \AppBundle\Entity\Image
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Image")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     * })
     */
    private $image;



    /**
     * Set montantHt
     *
     * @param float $montantHt
     *
     * @return TvaSaisie2
     */
    public function setMontantHt($montantHt)
    {
        $this->montantHt = $montantHt;

        return $this;
    }

    /**
     * Get montantHt
     *
     * @return float
     */
    public function getMontantHt()
    {
        return $this->montantHt;
    }

    /**
     * Set tauxTva
     *
     * @param float $tauxTva
     *
     * @return TvaSaisie2
     */
    public function setTauxTva($tauxTva)
    {
        $this->tauxTva = $tauxTva;

        return $this;
    }

    /**
     * Get tauxTva
     *
     * @return float
     */
    public function getTauxTva()
    {
        return $this->tauxTva;
    }

    /**
     * Set nature
     *
     * @param string $nature
     *
     * @return TvaSaisie2
     */
    public function setNature($nature)
    {
        $this->nature = $nature;

        return $this;
    }

    /**
     * Get nature
     *
     * @return string
     */
    public function getNature()
    {
        return $this->nature;
    }

    /**
     * Set periodeDeb
     *
     * @param \DateTime $periodeDeb
     *
     * @return TvaSaisie2
     */
    public function setPeriodeDeb($periodeDeb)
    {
        $this->periodeDeb = $periodeDeb;

        return $this;
    }

    /**
     * Get periodeDeb
     *
     * @return \DateTime
     */
    public function getPeriodeDeb()
    {
        return $this->periodeDeb;
    }

    /**
     * Set periodeFin
     *
     * @param \DateTime $periodeFin
     *
     * @return TvaSaisie2
     */
    public function setPeriodeFin($periodeFin)
    {
        $this->periodeFin = $periodeFin;

        return $this;
    }

    /**
     * Get periodeFin
     *
     * @return \DateTime
     */
    public function getPeriodeFin()
    {
        return $this->periodeFin;
    }

    /**
     * Set dateLivraison
     *
     * @param \DateTime $dateLivraison
     *
     * @return TvaSaisie2
     */
    public function setDateLivraison($dateLivraison)
    {
        $this->dateLivraison = $dateLivraison;

        return $this;
    }

    /**
     * Get dateLivraison
     *
     * @return \DateTime
     */
    public function getDateLivraison()
    {
        return $this->dateLivraison;
    }

    /**
     * Set prelibelle
     *
     * @param string $prelibelle
     *
     * @return TvaSaisie2
     */
    public function setPrelibelle($prelibelle)
    {
        $this->prelibelle = $prelibelle;

        return $this;
    }

    /**
     * Get prelibelle
     *
     * @return string
     */
    public function getPrelibelle()
    {
        return $this->prelibelle;
    }

    /**
     * Set montantTtc
     *
     * @param float $montantTtc
     *
     * @return TvaSaisie2
     */
    public function setMontantTtc($montantTtc)
    {
        $this->montantTtc = $montantTtc;

        return $this;
    }

    /**
     * Get montantTtc
     *
     * @return float
     */
    public function getMontantTtc()
    {
        return $this->montantTtc;
    }

    /**
     * Set soldeInitial
     *
     * @param float $soldeInitial
     *
     * @return TvaSaisie2
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
     * Set entreeSortie
     *
     * @param integer $entreeSortie
     *
     * @return TvaSaisie2
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
     * Set soldeFinal
     *
     * @param float $soldeFinal
     *
     * @return TvaSaisie2
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
     * @return TvaSaisie2
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
     * Set engagementTresorerie
     *
     * @param integer $engagementTresorerie
     *
     * @return TvaSaisie2
     */
    public function setEngagementTresorerie($engagementTresorerie)
    {
        $this->engagementTresorerie = $engagementTresorerie;

        return $this;
    }

    /**
     * Get engagementTresorerie
     *
     * @return integer
     */
    public function getEngagementTresorerie()
    {
        return $this->engagementTresorerie;
    }

    /**
     * Set montantTtcDevise
     *
     * @param float $montantTtcDevise
     *
     * @return TvaSaisie2
     */
    public function setMontantTtcDevise($montantTtcDevise)
    {
        $this->montantTtcDevise = $montantTtcDevise;

        return $this;
    }

    /**
     * Get montantTtcDevise
     *
     * @return float
     */
    public function getMontantTtcDevise()
    {
        return $this->montantTtcDevise;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return TvaSaisie2
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set nbreCouvert
     *
     * @param integer $nbreCouvert
     *
     * @return TvaSaisie2
     */
    public function setNbreCouvert($nbreCouvert)
    {
        $this->nbreCouvert = $nbreCouvert;

        return $this;
    }

    /**
     * Get nbreCouvert
     *
     * @return integer
     */
    public function getNbreCouvert()
    {
        return $this->nbreCouvert;
    }

    /**
     * Set distance
     *
     * @param float $distance
     *
     * @return TvaSaisie2
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Get distance
     *
     * @return float
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set groupe
     *
     * @param integer $groupe
     *
     * @return TvaSaisie2
     */
    public function setGroupe($groupe)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return integer
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set trajet
     *
     * @param integer $trajet
     *
     * @return TvaSaisie2
     */
    public function setTrajet($trajet)
    {
        $this->trajet = $trajet;

        return $this;
    }

    /**
     * Get trajet
     *
     * @return integer
     */
    public function getTrajet()
    {
        return $this->trajet;
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
     * Set sousnature
     *
     * @param \AppBundle\Entity\Sousnature $sousnature
     *
     * @return TvaSaisie2
     */
    public function setSousnature(\AppBundle\Entity\Sousnature $sousnature = null)
    {
        $this->sousnature = $sousnature;

        return $this;
    }

    /**
     * Get sousnature
     *
     * @return \AppBundle\Entity\Sousnature
     */
    public function getSousnature()
    {
        return $this->sousnature;
    }

    /**
     * Set pccTva
     *
     * @param \AppBundle\Entity\Pcc $pccTva
     *
     * @return TvaSaisie2
     */
    public function setPccTva(\AppBundle\Entity\Pcc $pccTva = null)
    {
        $this->pccTva = $pccTva;

        return $this;
    }

    /**
     * Get pccTva
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getPccTva()
    {
        return $this->pccTva;
    }

    /**
     * Set pccBilan
     *
     * @param \AppBundle\Entity\Pcc $pccBilan
     *
     * @return TvaSaisie2
     */
    public function setPccBilan(\AppBundle\Entity\Pcc $pccBilan = null)
    {
        $this->pccBilan = $pccBilan;

        return $this;
    }

    /**
     * Get pccBilan
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getPccBilan()
    {
        return $this->pccBilan;
    }

    /**
     * Set soussouscategorie
     *
     * @param \AppBundle\Entity\Soussouscategorie $soussouscategorie
     *
     * @return TvaSaisie2
     */
    public function setSoussouscategorie(\AppBundle\Entity\Soussouscategorie $soussouscategorie = null)
    {
        $this->soussouscategorie = $soussouscategorie;

        return $this;
    }

    /**
     * Get soussouscategorie
     *
     * @return \AppBundle\Entity\Soussouscategorie
     */
    public function getSoussouscategorie()
    {
        return $this->soussouscategorie;
    }

    /**
     * Set tiers
     *
     * @param \AppBundle\Entity\Tiers $tiers
     *
     * @return TvaSaisie2
     */
    public function setTiers(\AppBundle\Entity\Tiers $tiers = null)
    {
        $this->tiers = $tiers;

        return $this;
    }

    /**
     * Get tiers
     *
     * @return \AppBundle\Entity\Tiers
     */
    public function getTiers()
    {
        return $this->tiers;
    }

    /**
     * Set vehicule
     *
     * @param \AppBundle\Entity\Vehicule $vehicule
     *
     * @return TvaSaisie2
     */
    public function setVehicule(\AppBundle\Entity\Vehicule $vehicule = null)
    {
        $this->vehicule = $vehicule;

        return $this;
    }

    /**
     * Get vehicule
     *
     * @return \AppBundle\Entity\Vehicule
     */
    public function getVehicule()
    {
        return $this->vehicule;
    }

    /**
     * Set typeVente
     *
     * @param \AppBundle\Entity\TypeVente $typeVente
     *
     * @return TvaSaisie2
     */
    public function setTypeVente(\AppBundle\Entity\TypeVente $typeVente = null)
    {
        $this->typeVente = $typeVente;

        return $this;
    }

    /**
     * Get typeVente
     *
     * @return \AppBundle\Entity\TypeVente
     */
    public function getTypeVente()
    {
        return $this->typeVente;
    }

    /**
     * Set tvaTaux
     *
     * @param \AppBundle\Entity\TvaTaux $tvaTaux
     *
     * @return TvaSaisie2
     */
    public function setTvaTaux(\AppBundle\Entity\TvaTaux $tvaTaux = null)
    {
        $this->tvaTaux = $tvaTaux;

        return $this;
    }

    /**
     * Get tvaTaux
     *
     * @return \AppBundle\Entity\TvaTaux
     */
    public function getTvaTaux()
    {
        return $this->tvaTaux;
    }

    /**
     * Set pcc
     *
     * @param \AppBundle\Entity\Pcc $pcc
     *
     * @return TvaSaisie2
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
     * Set pays
     *
     * @param \AppBundle\Entity\Pays $pays
     *
     * @return TvaSaisie2
     */
    public function setPays(\AppBundle\Entity\Pays $pays = null)
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * Get pays
     *
     * @return \AppBundle\Entity\Pays
     */
    public function getPays()
    {
        return $this->pays;
    }

    /**
     * Set codeAnalytique
     *
     * @param \AppBundle\Entity\CodeAnalytique $codeAnalytique
     *
     * @return TvaSaisie2
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
     * Set caisseType
     *
     * @param \AppBundle\Entity\CaisseType $caisseType
     *
     * @return TvaSaisie2
     */
    public function setCaisseType(\AppBundle\Entity\CaisseType $caisseType = null)
    {
        $this->caisseType = $caisseType;

        return $this;
    }

    /**
     * Get caisseType
     *
     * @return \AppBundle\Entity\CaisseType
     */
    public function getCaisseType()
    {
        return $this->caisseType;
    }

    /**
     * Set caisseNature
     *
     * @param \AppBundle\Entity\CaisseNature $caisseNature
     *
     * @return TvaSaisie2
     */
    public function setCaisseNature(\AppBundle\Entity\CaisseNature $caisseNature = null)
    {
        $this->caisseNature = $caisseNature;

        return $this;
    }

    /**
     * Get caisseNature
     *
     * @return \AppBundle\Entity\CaisseNature
     */
    public function getCaisseNature()
    {
        return $this->caisseNature;
    }

    /**
     * Set conditionDepense
     *
     * @param \AppBundle\Entity\ConditionDepense $conditionDepense
     *
     * @return TvaSaisie2
     */
    public function setConditionDepense(\AppBundle\Entity\ConditionDepense $conditionDepense = null)
    {
        $this->conditionDepense = $conditionDepense;

        return $this;
    }

    /**
     * Get conditionDepense
     *
     * @return \AppBundle\Entity\ConditionDepense
     */
    public function getConditionDepense()
    {
        return $this->conditionDepense;
    }

    /**
     * Set devise
     *
     * @param \AppBundle\Entity\Devise $devise
     *
     * @return TvaSaisie2
     */
    public function setDevise(\AppBundle\Entity\Devise $devise = null)
    {
        $this->devise = $devise;

        return $this;
    }

    /**
     * Get devise
     *
     * @return \AppBundle\Entity\Devise
     */
    public function getDevise()
    {
        return $this->devise;
    }

    /**
     * Set nature2
     *
     * @param \AppBundle\Entity\Nature $nature2
     *
     * @return TvaSaisie2
     */
    public function setNature2(\AppBundle\Entity\Nature $nature2 = null)
    {
        $this->nature2 = $nature2;

        return $this;
    }

    /**
     * Get nature2
     *
     * @return \AppBundle\Entity\Nature
     */
    public function getNature2()
    {
        return $this->nature2;
    }

    /**
     * Set modeReglement
     *
     * @param \AppBundle\Entity\ModeReglement $modeReglement
     *
     * @return TvaSaisie2
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
     * @return TvaSaisie2
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
     * Set image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return TvaSaisie2
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
}
