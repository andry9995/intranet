<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DetailFacture2
 *
 * @ORM\Table(name="detail_facture2", indexes={@ORM\Index(name="fk_detfact_image_id1_idx", columns={"image_id"})})
 * @ORM\Entity
 */
class DetailFacture2
{
    /**
     * @var string
     *
     * @ORM\Column(name="num_commande", type="string", length=45, nullable=true)
     */
    private $numCommande;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_commande", type="date", nullable=true)
     */
    private $dateCommande;

    /**
     * @var string
     *
     * @ORM\Column(name="num_livraison", type="string", length=45, nullable=true)
     */
    private $numLivraison;

    /**
     * @var string
     *
     * @ORM\Column(name="num_bl_frns", type="string", length=45, nullable=true)
     */
    private $numBlFrns;

    /**
     * @var string
     *
     * @ORM\Column(name="type_libelle", type="string", length=45, nullable=true)
     */
    private $typeLibelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="num_semaine", type="integer", nullable=true)
     */
    private $numSemaine;

    /**
     * @var float
     *
     * @ORM\Column(name="taux_escompte", type="float", precision=10, scale=0, nullable=true)
     */
    private $tauxEscompte;

    /**
     * @var float
     *
     * @ORM\Column(name="montant_escompte", type="float", precision=10, scale=0, nullable=true)
     */
    private $montantEscompte;

    /**
     * @var float
     *
     * @ORM\Column(name="net_fin", type="float", precision=10, scale=0, nullable=true)
     */
    private $netFin;

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
     * @ORM\Column(name="axe_analytique1", type="string", length=45, nullable=true)
     */
    private $axeAnalytique1;

    /**
     * @var string
     *
     * @ORM\Column(name="axe_analytique2", type="string", length=45, nullable=true)
     */
    private $axeAnalytique2;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_bl_frns", type="date", nullable=true)
     */
    private $dateBlFrns;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * Set numCommande
     *
     * @param string $numCommande
     *
     * @return DetailFacture2
     */
    public function setNumCommande($numCommande)
    {
        $this->numCommande = $numCommande;

        return $this;
    }

    /**
     * Get numCommande
     *
     * @return string
     */
    public function getNumCommande()
    {
        return $this->numCommande;
    }

    /**
     * Set dateCommande
     *
     * @param \DateTime $dateCommande
     *
     * @return DetailFacture2
     */
    public function setDateCommande($dateCommande)
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    /**
     * Get dateCommande
     *
     * @return \DateTime
     */
    public function getDateCommande()
    {
        return $this->dateCommande;
    }

    /**
     * Set numLivraison
     *
     * @param string $numLivraison
     *
     * @return DetailFacture2
     */
    public function setNumLivraison($numLivraison)
    {
        $this->numLivraison = $numLivraison;

        return $this;
    }

    /**
     * Get numLivraison
     *
     * @return string
     */
    public function getNumLivraison()
    {
        return $this->numLivraison;
    }

    /**
     * Set numBlFrns
     *
     * @param string $numBlFrns
     *
     * @return DetailFacture2
     */
    public function setNumBlFrns($numBlFrns)
    {
        $this->numBlFrns = $numBlFrns;

        return $this;
    }

    /**
     * Get numBlFrns
     *
     * @return string
     */
    public function getNumBlFrns()
    {
        return $this->numBlFrns;
    }

    /**
     * Set typeLibelle
     *
     * @param string $typeLibelle
     *
     * @return DetailFacture2
     */
    public function setTypeLibelle($typeLibelle)
    {
        $this->typeLibelle = $typeLibelle;

        return $this;
    }

    /**
     * Get typeLibelle
     *
     * @return string
     */
    public function getTypeLibelle()
    {
        return $this->typeLibelle;
    }

    /**
     * Set numSemaine
     *
     * @param integer $numSemaine
     *
     * @return DetailFacture2
     */
    public function setNumSemaine($numSemaine)
    {
        $this->numSemaine = $numSemaine;

        return $this;
    }

    /**
     * Get numSemaine
     *
     * @return integer
     */
    public function getNumSemaine()
    {
        return $this->numSemaine;
    }

    /**
     * Set tauxEscompte
     *
     * @param float $tauxEscompte
     *
     * @return DetailFacture2
     */
    public function setTauxEscompte($tauxEscompte)
    {
        $this->tauxEscompte = $tauxEscompte;

        return $this;
    }

    /**
     * Get tauxEscompte
     *
     * @return float
     */
    public function getTauxEscompte()
    {
        return $this->tauxEscompte;
    }

    /**
     * Set montantEscompte
     *
     * @param float $montantEscompte
     *
     * @return DetailFacture2
     */
    public function setMontantEscompte($montantEscompte)
    {
        $this->montantEscompte = $montantEscompte;

        return $this;
    }

    /**
     * Get montantEscompte
     *
     * @return float
     */
    public function getMontantEscompte()
    {
        return $this->montantEscompte;
    }

    /**
     * Set netFin
     *
     * @param float $netFin
     *
     * @return DetailFacture2
     */
    public function setNetFin($netFin)
    {
        $this->netFin = $netFin;

        return $this;
    }

    /**
     * Get netFin
     *
     * @return float
     */
    public function getNetFin()
    {
        return $this->netFin;
    }

    /**
     * Set montantHt
     *
     * @param float $montantHt
     *
     * @return DetailFacture2
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
     * @return DetailFacture2
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
     * Set axeAnalytique1
     *
     * @param string $axeAnalytique1
     *
     * @return DetailFacture2
     */
    public function setAxeAnalytique1($axeAnalytique1)
    {
        $this->axeAnalytique1 = $axeAnalytique1;

        return $this;
    }

    /**
     * Get axeAnalytique1
     *
     * @return string
     */
    public function getAxeAnalytique1()
    {
        return $this->axeAnalytique1;
    }

    /**
     * Set axeAnalytique2
     *
     * @param string $axeAnalytique2
     *
     * @return DetailFacture2
     */
    public function setAxeAnalytique2($axeAnalytique2)
    {
        $this->axeAnalytique2 = $axeAnalytique2;

        return $this;
    }

    /**
     * Get axeAnalytique2
     *
     * @return string
     */
    public function getAxeAnalytique2()
    {
        return $this->axeAnalytique2;
    }

    /**
     * Set dateBlFrns
     *
     * @param \DateTime $dateBlFrns
     *
     * @return DetailFacture2
     */
    public function setDateBlFrns($dateBlFrns)
    {
        $this->dateBlFrns = $dateBlFrns;

        return $this;
    }

    /**
     * Get dateBlFrns
     *
     * @return \DateTime
     */
    public function getDateBlFrns()
    {
        return $this->dateBlFrns;
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
     * Set image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return DetailFacture2
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
