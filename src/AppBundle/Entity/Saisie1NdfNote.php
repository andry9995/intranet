<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Saisie1NdfNote
 *
 * @ORM\Table(name="saisie1_ndf_note", indexes={@ORM\Index(name="fk_saisie1_ndf_image1_idx", columns={"image_id"}), @ORM\Index(name="fk_saisie1_ndf_utilisateur1_idx", columns={"ndf_utilisateur_id"})})
 * @ORM\Entity
 */
class Saisie1NdfNote
{
    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=45, nullable=false)
     */
    private $libelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="annee", type="integer", nullable=true)
     */
    private $annee;

    /**
     * @var integer
     *
     * @ORM\Column(name="mois_du", type="integer", nullable=true)
     */
    private $moisDu;

    /**
     * @var integer
     *
     * @ORM\Column(name="mois_au", type="integer", nullable=true)
     */
    private $moisAu;

    /**
     * @var integer
     *
     * @ORM\Column(name="remboursable", type="integer", nullable=false)
     */
    private $remboursable = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="facturable", type="integer", nullable=false)
     */
    private $facturable = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\NdfUtilisateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\NdfUtilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ndf_utilisateur_id", referencedColumnName="id")
     * })
     */
    private $ndfUtilisateur;

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
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Saisie1NdfNote
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
     * Set annee
     *
     * @param integer $annee
     *
     * @return Saisie1NdfNote
     */
    public function setAnnee($annee)
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * Get annee
     *
     * @return integer
     */
    public function getAnnee()
    {
        return $this->annee;
    }

    /**
     * Set moisDu
     *
     * @param integer $moisDu
     *
     * @return Saisie1NdfNote
     */
    public function setMoisDu($moisDu)
    {
        $this->moisDu = $moisDu;

        return $this;
    }

    /**
     * Get moisDu
     *
     * @return integer
     */
    public function getMoisDu()
    {
        return $this->moisDu;
    }

    /**
     * Set moisAu
     *
     * @param integer $moisAu
     *
     * @return Saisie1NdfNote
     */
    public function setMoisAu($moisAu)
    {
        $this->moisAu = $moisAu;

        return $this;
    }

    /**
     * Get moisAu
     *
     * @return integer
     */
    public function getMoisAu()
    {
        return $this->moisAu;
    }

    /**
     * Set remboursable
     *
     * @param integer $remboursable
     *
     * @return Saisie1NdfNote
     */
    public function setRemboursable($remboursable)
    {
        $this->remboursable = $remboursable;

        return $this;
    }

    /**
     * Get remboursable
     *
     * @return integer
     */
    public function getRemboursable()
    {
        return $this->remboursable;
    }

    /**
     * Set facturable
     *
     * @param integer $facturable
     *
     * @return Saisie1NdfNote
     */
    public function setFacturable($facturable)
    {
        $this->facturable = $facturable;

        return $this;
    }

    /**
     * Get facturable
     *
     * @return integer
     */
    public function getFacturable()
    {
        return $this->facturable;
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
     * Set ndfUtilisateur
     *
     * @param \AppBundle\Entity\NdfUtilisateur $ndfUtilisateur
     *
     * @return Saisie1NdfNote
     */
    public function setNdfUtilisateur(\AppBundle\Entity\NdfUtilisateur $ndfUtilisateur = null)
    {
        $this->ndfUtilisateur = $ndfUtilisateur;

        return $this;
    }

    /**
     * Get ndfUtilisateur
     *
     * @return \AppBundle\Entity\NdfUtilisateur
     */
    public function getNdfUtilisateur()
    {
        return $this->ndfUtilisateur;
    }

    /**
     * Set image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return Saisie1NdfNote
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
