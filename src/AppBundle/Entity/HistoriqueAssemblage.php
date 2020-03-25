<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HistoriqueAssemblage
 *
 * @ORM\Table(name="historique_assemblage", indexes={@ORM\Index(name="fk_historique_assemblage_originiale_idx", columns={"image_originale"}), @ORM\Index(name="fk_historique_assemblage_finale_idx", columns={"image_finale"}), @ORM\Index(name="fk_historique_assemblage_operateur_idx", columns={"operateur_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HistoriqueAssemblageRepository")
 */
class HistoriqueAssemblage
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_assemblage", type="datetime", nullable=false)
     */
    private $dateAssemblage;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_desassemblage", type="datetime", nullable=true)
     */
    private $dateDesassemblage;

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
     *   @ORM\JoinColumn(name="image_originale", referencedColumnName="id")
     * })
     */
    private $imageOriginale;

    /**
     * @var \AppBundle\Entity\Image
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Image")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="image_finale", referencedColumnName="id")
     * })
     */
    private $imageFinale;

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
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="desassemblage_operateur_id", referencedColumnName="id")
*     })
     */
    private $desassemblageOperateur;


    /**
     * Set dateAssemblage
     *
     * @param \DateTime $dateAssemblage
     *
     * @return HistoriqueAssemblage
     */
    public function setDateAssemblage($dateAssemblage)
    {
        $this->dateAssemblage = $dateAssemblage;

        return $this;
    }

    /**
     * Get dateAssemblage
     *
     * @return \DateTime
     */
    public function getDateAssemblage()
    {
        return $this->dateAssemblage;
    }

    /**
     * @param $dateDesassemblage
     * @return $this
     */
    public function setDateDesassemblage($dateDesassemblage)
    {
        $this->dateDesassemblage = $dateDesassemblage;

        return $this;
    }

    /**
     * Get dateDesassemblage
     *
     * @return \DateTime
     */
    public function getDateDesassemblage()
    {
        return $this->dateDesassemblage;
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
     * Set imageOriginale
     *
     * @param \AppBundle\Entity\Image $imageOriginale
     *
     * @return HistoriqueAssemblage
     */
    public function setImageOriginale(\AppBundle\Entity\Image $imageOriginale = null)
    {
        $this->imageOriginale = $imageOriginale;

        return $this;
    }

    /**
     * Get imageOriginale
     *
     * @return \AppBundle\Entity\Image
     */
    public function getImageOriginale()
    {
        return $this->imageOriginale;
    }

    /**
     * Set imageFinale
     *
     * @param \AppBundle\Entity\Image $imageFinale
     *
     * @return HistoriqueAssemblage
     */
    public function setImageFinale(\AppBundle\Entity\Image $imageFinale = null)
    {
        $this->imageFinale = $imageFinale;

        return $this;
    }

    /**
     * Get imageFinale
     *
     * @return \AppBundle\Entity\Image
     */
    public function getImageFinale()
    {
        return $this->imageFinale;
    }

    /**
     * Set operateur
     *
     * @param \AppBundle\Entity\Operateur $operateur
     *
     * @return HistoriqueAssemblage
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
     * @param Operateur|null $desassemblageOperateur
     * @return $this
     */
    public function setDesassemblageOperateur(\AppBundle\Entity\Operateur $desassemblageOperateur = null)
    {
        $this->desassemblageOperateur = $desassemblageOperateur;

        return $this;
    }

    /**
     * Get desassemblageOperateur
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getDesassemblageOperateur()
    {
        return $this->desassemblageOperateur;
    }
}
