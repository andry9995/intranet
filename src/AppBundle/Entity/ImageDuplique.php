<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ImageDuplique
 *
 * @ORM\Table(name="image_duplique", indexes={@ORM\Index(name="fk_image_duplique_image1_idx", columns={"image_id"}), @ORM\Index(name="fk_image_duplique_operateur1_idx", columns={"operateur_id"}), @ORM\Index(name="fk_image_duplique_souscategorie1_idx", columns={"souscategorie_id"}), @ORM\Index(name="fk_image_duplique_soussouscategorie1_idx", columns={"soussouscategorie_id"})})
 * @ORM\Entity
 */
class ImageDuplique
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_duplication", type="datetime", nullable=false)
     */
    private $dateDuplication;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=45, nullable=true)
     */
    private $commentaire;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * @var \AppBundle\Entity\Souscategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Souscategorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="souscategorie_id", referencedColumnName="id")
     * })
     */
    private $souscategorie;

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
     * Set dateDuplication
     *
     * @param \DateTime $dateDuplication
     *
     * @return ImageDuplique
     */
    public function setDateDuplication($dateDuplication)
    {
        $this->dateDuplication = $dateDuplication;

        return $this;
    }

    /**
     * Get dateDuplication
     *
     * @return \DateTime
     */
    public function getDateDuplication()
    {
        return $this->dateDuplication;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     *
     * @return ImageDuplique
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string
     */
    public function getCommentaire()
    {
        return $this->commentaire;
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
     * Set soussouscategorie
     *
     * @param \AppBundle\Entity\Soussouscategorie $soussouscategorie
     *
     * @return ImageDuplique
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
     * Set souscategorie
     *
     * @param \AppBundle\Entity\Souscategorie $souscategorie
     *
     * @return ImageDuplique
     */
    public function setSouscategorie(\AppBundle\Entity\Souscategorie $souscategorie = null)
    {
        $this->souscategorie = $souscategorie;

        return $this;
    }

    /**
     * Get souscategorie
     *
     * @return \AppBundle\Entity\Souscategorie
     */
    public function getSouscategorie()
    {
        return $this->souscategorie;
    }

    /**
     * Set operateur
     *
     * @param \AppBundle\Entity\Operateur $operateur
     *
     * @return ImageDuplique
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
     * @return ImageDuplique
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
