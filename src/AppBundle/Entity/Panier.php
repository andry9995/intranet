<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Panier
 *
 * @ORM\Table(name="panier", indexes={@ORM\Index(name="fk_panier_image1_idx", columns={"image_id"}), @ORM\Index(name="fk_panier_operateur1_idx", columns={"operateur_id"}), @ORM\Index(name="fk_panier_etape_traitement1_idx", columns={"etape_traitement_id"}), @ORM\Index(name="fk_categorie_id_idx", columns={"categorie_id"}), @ORM\Index(name="fk_panier_dossier1_idx", columns={"dossier_id"}), @ORM\Index(name="fk_panier_oppartage_id_idx", columns={"op_partage_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PanierRepository")
 */
class Panier
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_panier", type="date", nullable=false)
     */
    private $datePanier;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="remarque", type="string", length=255, nullable=true)
     */
    private $remarque;

    /**
     * @var integer
     *
     * @ORM\Column(name="op_partage_id", type="integer", nullable=false)
     */
    private $opPartageId;

    /**
     * @var integer
     *
     * @ORM\Column(name="fini", type="integer", nullable=false)
     */
    private $fini = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * @var \AppBundle\Entity\Image
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Image")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     * })
     */
    private $image;

    /**
     * @var \AppBundle\Entity\EtapeTraitement
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EtapeTraitement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="etape_traitement_id", referencedColumnName="id")
     * })
     */
    private $etapeTraitement;

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
     * @var \AppBundle\Entity\Categorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Categorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="categorie_id", referencedColumnName="id")
     * })
     */
    private $categorie;



    /**
     * Set datePanier
     *
     * @param \DateTime $datePanier
     *
     * @return Panier
     */
    public function setDatePanier($datePanier)
    {
        $this->datePanier = $datePanier;

        return $this;
    }

    /**
     * Get datePanier
     *
     * @return \DateTime
     */
    public function getDatePanier()
    {
        return $this->datePanier;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Panier
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
     * Set remarque
     *
     * @param string $remarque
     *
     * @return Panier
     */
    public function setRemarque($remarque)
    {
        $this->remarque = $remarque;

        return $this;
    }

    /**
     * Get remarque
     *
     * @return string
     */
    public function getRemarque()
    {
        return $this->remarque;
    }

    /**
     * Set opPartageId
     *
     * @param integer $opPartageId
     *
     * @return Panier
     */
    public function setOpPartageId($opPartageId)
    {
        $this->opPartageId = $opPartageId;

        return $this;
    }

    /**
     * Get opPartageId
     *
     * @return integer
     */
    public function getOpPartageId()
    {
        return $this->opPartageId;
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
     * Set operateur
     *
     * @param \AppBundle\Entity\Operateur $operateur
     *
     * @return Panier
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
     * Set image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return Panier
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
     * Set etapeTraitement
     *
     * @param \AppBundle\Entity\EtapeTraitement $etapeTraitement
     *
     * @return Panier
     */
    public function setEtapeTraitement(\AppBundle\Entity\EtapeTraitement $etapeTraitement = null)
    {
        $this->etapeTraitement = $etapeTraitement;

        return $this;
    }

    /**
     * Get etapeTraitement
     *
     * @return \AppBundle\Entity\EtapeTraitement
     */
    public function getEtapeTraitement()
    {
        return $this->etapeTraitement;
    }

    /**
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return Panier
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
     * Set categorie
     *
     * @param \AppBundle\Entity\Categorie $categorie
     *
     * @return Panier
     */
    public function setCategorie(\AppBundle\Entity\Categorie $categorie = null)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return \AppBundle\Entity\Categorie
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Set fini
     *
     * @param integer $fini
     *
     * @return Panier
     */
    public function setFini($fini)
    {
        $this->fini = $fini;

        return $this;
    }

    /**
     * Get fini
     *
     * @return integer
     */
    public function getFini()
    {
        return $this->fini;
    }
}
