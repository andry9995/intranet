<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProcessDossierCategorie
 *
 * @ORM\Table(name="process_dossier_categorie", indexes={@ORM\Index(name="fk_processdossiercateg_dossierid_idx", columns={"dossier_id"}), @ORM\Index(name="fk_processdossiercateg_categid_idx", columns={"categorie_id"}), @ORM\Index(name="fk_processdossiercateg_osid_idx", columns={"operateur_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProcessDossierCategorieRepository")
 */
class ProcessDossierCategorie
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime", nullable=true)
     */
    private $dateCreation = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="actif", type="integer", nullable=true)
     */
    private $actif = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="exercice", type="integer", nullable=true)
     */
    private $exercice;

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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return ProcessDossierCategorie
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set actif
     *
     * @param integer $actif
     *
     * @return ProcessDossierCategorie
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get actif
     *
     * @return integer
     */
    public function getActif()
    {
        return $this->actif;
    }

    /**
     * Set exercice
     *
     * @param integer $exercice
     *
     * @return ProcessDossierCategorie
     */
    public function setExercice($exercice)
    {
        $this->exercice = $exercice;

        return $this;
    }

    /**
     * Get exercice
     *
     * @return integer
     */
    public function getExercice()
    {
        return $this->exercice;
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
     * @return ProcessDossierCategorie
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
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return ProcessDossierCategorie
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
     * @return ProcessDossierCategorie
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
}
