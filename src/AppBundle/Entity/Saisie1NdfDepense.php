<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Saisie1NdfDepense
 *
 * @ORM\Table(name="saisie1_ndf_depense", indexes={@ORM\Index(name="saisie1_ndf_depense_saisie1_ndf_note1_idx", columns={"saisie1_ndf_note_id"}), @ORM\Index(name="fk_saisie1_ndf_depense_type_frais1_idx", columns={"type_frais_id"}), @ORM\Index(name="fk_saisie1_ndf_depense_mode_reglement1_idx", columns={"mode_reglement_id"}), @ORM\Index(name="fk_saisie1_ndf_depense_pays1_idx", columns={"pays_id"}), @ORM\Index(name="fk_saisie1_ndf_depense_devise1_idx", columns={"devise_id"}), @ORM\Index(name="fk_saisie1_ndf_depense_tva_taux1_idx", columns={"tva_taux_id"}), @ORM\Index(name="fk_saisie1_ndf_depense_vehicule1_idx", columns={"ik_vehicule_id"}), @ORM\Index(name="fk_saisie1_ndf_depense_ttcpcc_idx", columns={"ttc_pcc_id"}), @ORM\Index(name="fk_saisie1_ndf_depense_tvapcc_idx", columns={"tva_pcc_id"}), @ORM\Index(name="fk_saisie1_ndf_depense_htpcc_idx", columns={"ht_pcc_id"}), @ORM\Index(name="fk_saisie1_ndf_depense_condition_depense1_idx", columns={"condition_depense_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SaisieNdfDepenseRepository")
 */
class Saisie1NdfDepense
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @var float
     *
     * @ORM\Column(name="ttc_devise", type="float", precision=10, scale=0, nullable=true)
     */
    private $ttcDevise;

    /**
     * @var float
     *
     * @ORM\Column(name="ttc", type="float", precision=10, scale=0, nullable=true)
     */
    private $ttc;

    /**
     * @var float
     *
     * @ORM\Column(name="ht", type="float", precision=10, scale=0, nullable=true)
     */
    private $ht;

    /**
     * @var float
     *
     * @ORM\Column(name="tva", type="float", precision=10, scale=0, nullable=true)
     */
    private $tva;

    /**
     * @var integer
     *
     * @ORM\Column(name="row_id", type="integer", nullable=false)
     */
    private $rowId;

    /**
     * @var integer
     *
     * @ORM\Column(name="ik_trajet", type="integer", nullable=true)
     */
    private $ikTrajet;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ik_periode_debut", type="date", nullable=true)
     */
    private $ikPeriodeDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ik_periode_fin", type="date", nullable=true)
     */
    private $ikPeriodeFin;

    /**
     * @var string
     *
     * @ORM\Column(name="ik_description", type="string", length=45, nullable=true)
     */
    private $ikDescription;

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
     *   @ORM\JoinColumn(name="tva_pcc_id", referencedColumnName="id")
     * })
     */
    private $tvaPcc;

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
     * @var \AppBundle\Entity\TypeFrais
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TypeFrais")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_frais_id", referencedColumnName="id")
     * })
     */
    private $typeFrais;

    /**
     * @var \AppBundle\Entity\Vehicule
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Vehicule")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ik_vehicule_id", referencedColumnName="id")
     * })
     */
    private $ikVehicule;

    /**
     * @var \AppBundle\Entity\Pcc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ttc_pcc_id", referencedColumnName="id")
     * })
     */
    private $ttcPcc;

    /**
     * @var \AppBundle\Entity\Saisie1NdfNote
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Saisie1NdfNote")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="saisie1_ndf_note_id", referencedColumnName="id")
     * })
     */
    private $saisie1NdfNote;

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
     * @var \AppBundle\Entity\Pcc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ht_pcc_id", referencedColumnName="id")
     * })
     */
    private $htPcc;

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
     * @var \AppBundle\Entity\Pays
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pays")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pays_id", referencedColumnName="id")
     * })
     */
    private $pays;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Saisie1NdfDepense
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
     * Set ttcDevise
     *
     * @param float $ttcDevise
     *
     * @return Saisie1NdfDepense
     */
    public function setTtcDevise($ttcDevise)
    {
        $this->ttcDevise = $ttcDevise;

        return $this;
    }

    /**
     * Get ttcDevise
     *
     * @return float
     */
    public function getTtcDevise()
    {
        return $this->ttcDevise;
    }

    /**
     * Set ttc
     *
     * @param float $ttc
     *
     * @return Saisie1NdfDepense
     */
    public function setTtc($ttc)
    {
        $this->ttc = $ttc;

        return $this;
    }

    /**
     * Get ttc
     *
     * @return float
     */
    public function getTtc()
    {
        return $this->ttc;
    }

    /**
     * Set ht
     *
     * @param float $ht
     *
     * @return Saisie1NdfDepense
     */
    public function setHt($ht)
    {
        $this->ht = $ht;

        return $this;
    }

    /**
     * Get ht
     *
     * @return float
     */
    public function getHt()
    {
        return $this->ht;
    }

    /**
     * Set tva
     *
     * @param float $tva
     *
     * @return Saisie1NdfDepense
     */
    public function setTva($tva)
    {
        $this->tva = $tva;

        return $this;
    }

    /**
     * Get tva
     *
     * @return float
     */
    public function getTva()
    {
        return $this->tva;
    }

    /**
     * Set rowId
     *
     * @param integer $rowId
     *
     * @return Saisie1NdfDepense
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
     * Set ikTrajet
     *
     * @param integer $ikTrajet
     *
     * @return Saisie1NdfDepense
     */
    public function setIkTrajet($ikTrajet)
    {
        $this->ikTrajet = $ikTrajet;

        return $this;
    }

    /**
     * Get ikTrajet
     *
     * @return integer
     */
    public function getIkTrajet()
    {
        return $this->ikTrajet;
    }

    /**
     * Set ikPeriodeDebut
     *
     * @param \DateTime $ikPeriodeDebut
     *
     * @return Saisie1NdfDepense
     */
    public function setIkPeriodeDebut($ikPeriodeDebut)
    {
        $this->ikPeriodeDebut = $ikPeriodeDebut;

        return $this;
    }

    /**
     * Get ikPeriodeDebut
     *
     * @return \DateTime
     */
    public function getIkPeriodeDebut()
    {
        return $this->ikPeriodeDebut;
    }

    /**
     * Set ikPeriodeFin
     *
     * @param \DateTime $ikPeriodeFin
     *
     * @return Saisie1NdfDepense
     */
    public function setIkPeriodeFin($ikPeriodeFin)
    {
        $this->ikPeriodeFin = $ikPeriodeFin;

        return $this;
    }

    /**
     * Get ikPeriodeFin
     *
     * @return \DateTime
     */
    public function getIkPeriodeFin()
    {
        return $this->ikPeriodeFin;
    }

    /**
     * Set ikDescription
     *
     * @param string $ikDescription
     *
     * @return Saisie1NdfDepense
     */
    public function setIkDescription($ikDescription)
    {
        $this->ikDescription = $ikDescription;

        return $this;
    }

    /**
     * Get ikDescription
     *
     * @return string
     */
    public function getIkDescription()
    {
        return $this->ikDescription;
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
     * Set tvaPcc
     *
     * @param \AppBundle\Entity\Pcc $tvaPcc
     *
     * @return Saisie1NdfDepense
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
     * Set tvaTaux
     *
     * @param \AppBundle\Entity\TvaTaux $tvaTaux
     *
     * @return Saisie1NdfDepense
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
     * Set typeFrais
     *
     * @param \AppBundle\Entity\TypeFrais $typeFrais
     *
     * @return Saisie1NdfDepense
     */
    public function setTypeFrais(\AppBundle\Entity\TypeFrais $typeFrais = null)
    {
        $this->typeFrais = $typeFrais;

        return $this;
    }

    /**
     * Get typeFrais
     *
     * @return \AppBundle\Entity\TypeFrais
     */
    public function getTypeFrais()
    {
        return $this->typeFrais;
    }

    /**
     * Set ikVehicule
     *
     * @param \AppBundle\Entity\Vehicule $ikVehicule
     *
     * @return Saisie1NdfDepense
     */
    public function setIkVehicule(\AppBundle\Entity\Vehicule $ikVehicule = null)
    {
        $this->ikVehicule = $ikVehicule;

        return $this;
    }

    /**
     * Get ikVehicule
     *
     * @return \AppBundle\Entity\Vehicule
     */
    public function getIkVehicule()
    {
        return $this->ikVehicule;
    }

    /**
     * Set ttcPcc
     *
     * @param \AppBundle\Entity\Pcc $ttcPcc
     *
     * @return Saisie1NdfDepense
     */
    public function setTtcPcc(\AppBundle\Entity\Pcc $ttcPcc = null)
    {
        $this->ttcPcc = $ttcPcc;

        return $this;
    }

    /**
     * Get ttcPcc
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getTtcPcc()
    {
        return $this->ttcPcc;
    }

    /**
     * Set saisie1NdfNote
     *
     * @param \AppBundle\Entity\Saisie1NdfNote $saisie1NdfNote
     *
     * @return Saisie1NdfDepense
     */
    public function setSaisie1NdfNote(\AppBundle\Entity\Saisie1NdfNote $saisie1NdfNote = null)
    {
        $this->saisie1NdfNote = $saisie1NdfNote;

        return $this;
    }

    /**
     * Get saisie1NdfNote
     *
     * @return \AppBundle\Entity\Saisie1NdfNote
     */
    public function getSaisie1NdfNote()
    {
        return $this->saisie1NdfNote;
    }

    /**
     * Set devise
     *
     * @param \AppBundle\Entity\Devise $devise
     *
     * @return Saisie1NdfDepense
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
     * Set htPcc
     *
     * @param \AppBundle\Entity\Pcc $htPcc
     *
     * @return Saisie1NdfDepense
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
     * Set modeReglement
     *
     * @param \AppBundle\Entity\ModeReglement $modeReglement
     *
     * @return Saisie1NdfDepense
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
     * Set pays
     *
     * @param \AppBundle\Entity\Pays $pays
     *
     * @return Saisie1NdfDepense
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
     * Set conditionDepense
     *
     * @param \AppBundle\Entity\ConditionDepense $conditionDepense
     *
     * @return Saisie1NdfDepense
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
}
