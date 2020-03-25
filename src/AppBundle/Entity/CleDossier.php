<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CleDossier
 *
 * @ORM\Table(name="cle_dossier", uniqueConstraints={@ORM\UniqueConstraint(name="unik_cle_dossier", columns={"cle_id", "dossier_id"})}, indexes={@ORM\Index(name="fk_cle_dossier_dossier_idx", columns={"dossier_id"}), @ORM\Index(name="fk_cle_dossier_cle_idx", columns={"cle_id"}), @ORM\Index(name="fk_cle_dossier_resultat_idx", columns={"resultat"}), @ORM\Index(name="fk_cle_dossier_tva_idx", columns={"tva"}), @ORM\Index(name="fk_cle_dossier_bilan_pcc_idx", columns={"bilan_pcc"}), @ORM\Index(name="fk_cle_dossier_tier_pcc_idx", columns={"bilan_tiers"})})
 * @ORM\Entity
 */
class CleDossier
{
    /**
     * @var string
     *
     * @ORM\Column(name="taux_tva", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $tauxTva = '0.00';

    /**
     * @var integer
     *
     * @ORM\Column(name="occurence", type="integer", nullable=false)
     */
    private $occurence = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="type_compta", type="integer", nullable=false)
     */
    private $typeCompta = '0';

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
     *   @ORM\JoinColumn(name="resultat", referencedColumnName="id")
     * })
     */
    private $resultat;

    /**
     * @var \AppBundle\Entity\Pcc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tva", referencedColumnName="id")
     * })
     */
    private $tva;

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
     * @var \AppBundle\Entity\Cle
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Cle")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cle_id", referencedColumnName="id")
     * })
     */
    private $cle;

    /**
     * @var \AppBundle\Entity\Tiers
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Tiers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bilan_tiers", referencedColumnName="id")
     * })
     */
    private $bilanTiers;

    /**
     * @var \AppBundle\Entity\Pcc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bilan_pcc", referencedColumnName="id")
     * })
     */
    private $bilanPcc;



    /**
     * Set tauxTva
     *
     * @param string $tauxTva
     *
     * @return CleDossier
     */
    public function setTauxTva($tauxTva)
    {
        $this->tauxTva = $tauxTva;

        return $this;
    }

    /**
     * Get tauxTva
     *
     * @return string
     */
    public function getTauxTva()
    {
        return $this->tauxTva;
    }

    /**
     * Set occurence
     *
     * @param integer $occurence
     *
     * @return CleDossier
     */
    public function setOccurence($occurence)
    {
        $this->occurence = $occurence;

        return $this;
    }

    /**
     * Get occurence
     *
     * @return integer
     */
    public function getOccurence()
    {
        return $this->occurence;
    }

    /**
     * Set typeCompta
     *
     * @param integer $typeCompta
     *
     * @return CleDossier
     */
    public function setTypeCompta($typeCompta)
    {
        $this->typeCompta = $typeCompta;

        return $this;
    }

    /**
     * Get typeCompta
     *
     * @return integer
     */
    public function getTypeCompta()
    {
        return $this->typeCompta;
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
     * Set resultat
     *
     * @param \AppBundle\Entity\Pcc $resultat
     *
     * @return CleDossier
     */
    public function setResultat(\AppBundle\Entity\Pcc $resultat = null)
    {
        $this->resultat = $resultat;

        return $this;
    }

    /**
     * Get resultat
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getResultat()
    {
        return $this->resultat;
    }

    /**
     * Set tva
     *
     * @param \AppBundle\Entity\Pcc $tva
     *
     * @return CleDossier
     */
    public function setTva(\AppBundle\Entity\Pcc $tva = null)
    {
        $this->tva = $tva;

        return $this;
    }

    /**
     * Get tva
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getTva()
    {
        return $this->tva;
    }

    /**
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return CleDossier
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
     * Set cle
     *
     * @param \AppBundle\Entity\Cle $cle
     *
     * @return CleDossier
     */
    public function setCle(\AppBundle\Entity\Cle $cle = null)
    {
        $this->cle = $cle;

        return $this;
    }

    /**
     * Get cle
     *
     * @return \AppBundle\Entity\Cle
     */
    public function getCle()
    {
        return $this->cle;
    }

    /**
     * Set bilanTiers
     *
     * @param \AppBundle\Entity\Tiers $bilanTiers
     *
     * @return CleDossier
     */
    public function setBilanTiers(\AppBundle\Entity\Tiers $bilanTiers = null)
    {
        $this->bilanTiers = $bilanTiers;

        return $this;
    }

    /**
     * Get bilanTiers
     *
     * @return \AppBundle\Entity\Tiers
     */
    public function getBilanTiers()
    {
        return $this->bilanTiers;
    }

    /**
     * Set bilanPcc
     *
     * @param \AppBundle\Entity\Pcc $bilanPcc
     *
     * @return CleDossier
     */
    public function setBilanPcc(\AppBundle\Entity\Pcc $bilanPcc = null)
    {
        $this->bilanPcc = $bilanPcc;

        return $this;
    }

    /**
     * Get bilanPcc
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getBilanPcc()
    {
        return $this->bilanPcc;
    }
}
