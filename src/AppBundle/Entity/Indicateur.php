<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Indicateur
 *
 * @ORM\Table(name="indicateur", uniqueConstraints={@ORM\UniqueConstraint(name="uniq_indicateur_libelle_inicateur_pack", columns={"libelle", "indicateur_pack_id", "key_dupliquer"})}, indexes={@ORM\Index(name="fk_indicateur_indicateur_pack1_idx", columns={"indicateur_pack_id"}), @ORM\Index(name="fk_indicateur_dossier1_idx", columns={"dossier_id"}), @ORM\Index(name="fk_indicateur_client1_idx", columns={"client_id"})})
 * @ORM\Entity
 */
class Indicateur
{
    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=150, nullable=true)
     */
    private $libelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_table", type="integer", nullable=false)
     */
    private $isTable = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="formule", type="string", length=250, nullable=false)
     */
    private $formule = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="type_operation", type="integer", nullable=false)
     */
    private $typeOperation = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="unite", type="string", length=20, nullable=true)
     */
    private $unite;

    /**
     * @var integer
     *
     * @ORM\Column(name="max", type="integer", nullable=false)
     */
    private $max = '-1';

    /**
     * @var integer
     *
     * @ORM\Column(name="row_number", type="integer", nullable=false)
     */
    private $rowNumber = '10';

    /**
     * @var integer
     *
     * @ORM\Column(name="col_number", type="integer", nullable=false)
     */
    private $colNumber = '5';

    /**
     * @var integer
     *
     * @ORM\Column(name="is_decimal", type="integer", nullable=false)
     */
    private $isDecimal = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=100, nullable=false)
     */
    private $description = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="analyse", type="integer", nullable=false)
     */
    private $analyse = '-1';

    /**
     * @var integer
     *
     * @ORM\Column(name="periode", type="integer", nullable=false)
     */
    private $periode = '15';

    /**
     * @var integer
     *
     * @ORM\Column(name="rang", type="integer", nullable=false)
     */
    private $rang = '1000';

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_affiche", type="string", length=250, nullable=false)
     */
    private $libelleAffiche = '';

    /**
     * @var string
     *
     * @ORM\Column(name="key_dupliquer", type="string", length=25, nullable=false)
     */
    private $keyDupliquer = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="theme", type="integer", nullable=false)
     */
    private $theme = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="valider", type="integer", nullable=false)
     */
    private $valider;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\IndicateurPack
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\IndicateurPack")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="indicateur_pack_id", referencedColumnName="id")
     * })
     */
    private $indicateurPack;

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
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Indicateur
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
     * Set isTable
     *
     * @param integer $isTable
     *
     * @return Indicateur
     */
    public function setIsTable($isTable)
    {
        $this->isTable = $isTable;

        return $this;
    }

    /**
     * Get isTable
     *
     * @return integer
     */
    public function getIsTable()
    {
        return $this->isTable;
    }

    /**
     * Set formule
     *
     * @param string $formule
     *
     * @return Indicateur
     */
    public function setFormule($formule)
    {
        $this->formule = $formule;

        return $this;
    }

    /**
     * Get formule
     *
     * @return string
     */
    public function getFormule()
    {
        return $this->formule;
    }

    /**
     * Set typeOperation
     *
     * @param integer $typeOperation
     *
     * @return Indicateur
     */
    public function setTypeOperation($typeOperation)
    {
        $this->typeOperation = $typeOperation;

        return $this;
    }

    /**
     * Get typeOperation
     *
     * @return integer
     */
    public function getTypeOperation()
    {
        return $this->typeOperation;
    }

    /**
     * Set unite
     *
     * @param string $unite
     *
     * @return Indicateur
     */
    public function setUnite($unite)
    {
        $this->unite = $unite;

        return $this;
    }

    /**
     * Get unite
     *
     * @return string
     */
    public function getUnite()
    {
        return $this->unite;
    }

    /**
     * Set max
     *
     * @param integer $max
     *
     * @return Indicateur
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * Get max
     *
     * @return integer
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Set rowNumber
     *
     * @param integer $rowNumber
     *
     * @return Indicateur
     */
    public function setRowNumber($rowNumber)
    {
        $this->rowNumber = $rowNumber;

        return $this;
    }

    /**
     * Get rowNumber
     *
     * @return integer
     */
    public function getRowNumber()
    {
        return $this->rowNumber;
    }

    /**
     * Set colNumber
     *
     * @param integer $colNumber
     *
     * @return Indicateur
     */
    public function setColNumber($colNumber)
    {
        $this->colNumber = $colNumber;

        return $this;
    }

    /**
     * Get colNumber
     *
     * @return integer
     */
    public function getColNumber()
    {
        return $this->colNumber;
    }

    /**
     * Set isDecimal
     *
     * @param integer $isDecimal
     *
     * @return Indicateur
     */
    public function setIsDecimal($isDecimal)
    {
        $this->isDecimal = $isDecimal;

        return $this;
    }

    /**
     * Get isDecimal
     *
     * @return integer
     */
    public function getIsDecimal()
    {
        return $this->isDecimal;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Indicateur
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
     * Set analyse
     *
     * @param integer $analyse
     *
     * @return Indicateur
     */
    public function setAnalyse($analyse)
    {
        $this->analyse = $analyse;

        return $this;
    }

    /**
     * Get analyse
     *
     * @return integer
     */
    public function getAnalyse()
    {
        return $this->analyse;
    }

    /**
     * Set periode
     *
     * @param integer $periode
     *
     * @return Indicateur
     */
    public function setPeriode($periode)
    {
        $this->periode = $periode;

        return $this;
    }

    /**
     * Get periode
     *
     * @return integer
     */
    public function getPeriode()
    {
        return $this->periode;
    }

    /**
     * Set rang
     *
     * @param integer $rang
     *
     * @return Indicateur
     */
    public function setRang($rang)
    {
        $this->rang = $rang;

        return $this;
    }

    /**
     * Get rang
     *
     * @return integer
     */
    public function getRang()
    {
        return $this->rang;
    }

    /**
     * Set libelleAffiche
     *
     * @param string $libelleAffiche
     *
     * @return Indicateur
     */
    public function setLibelleAffiche($libelleAffiche)
    {
        $this->libelleAffiche = $libelleAffiche;

        return $this;
    }

    /**
     * Get libelleAffiche
     *
     * @return string
     */
    public function getLibelleAffiche()
    {
        return $this->libelleAffiche;
    }

    /**
     * Set keyDupliquer
     *
     * @param string $keyDupliquer
     *
     * @return Indicateur
     */
    public function setKeyDupliquer($keyDupliquer)
    {
        $this->keyDupliquer = $keyDupliquer;

        return $this;
    }

    /**
     * Get keyDupliquer
     *
     * @return string
     */
    public function getKeyDupliquer()
    {
        return $this->keyDupliquer;
    }

    /**
     * Set theme
     *
     * @param integer $theme
     *
     * @return Indicateur
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme
     *
     * @return integer
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Set valider
     *
     * @param integer $valider
     *
     * @return Indicateur
     */
    public function setValider($valider)
    {
        $this->valider = $valider;

        return $this;
    }

    /**
     * Get valider
     *
     * @return integer
     */
    public function getValider()
    {
        return $this->valider;
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
     * Set indicateurPack
     *
     * @param \AppBundle\Entity\IndicateurPack $indicateurPack
     *
     * @return Indicateur
     */
    public function setIndicateurPack(\AppBundle\Entity\IndicateurPack $indicateurPack = null)
    {
        $this->indicateurPack = $indicateurPack;

        return $this;
    }

    /**
     * Get indicateurPack
     *
     * @return \AppBundle\Entity\IndicateurPack
     */
    public function getIndicateurPack()
    {
        return $this->indicateurPack;
    }

    /**
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return Indicateur
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
     * @return Indicateur
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
