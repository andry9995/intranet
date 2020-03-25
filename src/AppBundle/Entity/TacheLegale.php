<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TacheLegale
 *
 * @ORM\Table(name="tache_legale")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TacheLegaleRepository")
 */
class TacheLegale
{
    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=50, nullable=false)
     */
    private $nom;

    /**
     * @var array
     *
     * @ORM\Column(name="regime_fiscal", type="simple_array", nullable=true)
     */
    private $regimeFiscal;

    /**
     * @var array
     *
     * @ORM\Column(name="forme_activite", type="simple_array", nullable=true)
     */
    private $formeActivite;

    /**
     * @var array
     *
     * @ORM\Column(name="forme_juridique", type="simple_array", nullable=true)
     */
    private $formeJuridique;

    /**
     * @var array
     *
     * @ORM\Column(name="date_cloture", type="simple_array", nullable=true)
     */
    private $dateCloture;

    /**
     * @var string
     *
     * @ORM\Column(name="evenement_declencheur", type="text", length=65535, nullable=true)
     */
    private $evenementDeclencheur;

    /**
     * @var integer
     *
     * @ORM\Column(name="periode_declaration", type="integer", nullable=true)
     */
    private $periodeDeclaration;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     *  One TacheLegale has many actions
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\TacheLegaleAction", mappedBy="tacheLegale")
     */
    private $actions;

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return TacheLegale
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set regimeFiscal
     *
     * @param array $regimeFiscal
     *
     * @return TacheLegale
     */
    public function setRegimeFiscal($regimeFiscal)
    {
        $this->regimeFiscal = $regimeFiscal;

        return $this;
    }

    /**
     * Get regimeFiscal
     *
     * @return array
     */
    public function getRegimeFiscal()
    {
        return $this->regimeFiscal;
    }

    /**
     * Set formeActivite
     *
     * @param array $formeActivite
     *
     * @return TacheLegale
     */
    public function setFormeActivite($formeActivite)
    {
        $this->formeActivite = $formeActivite;

        return $this;
    }

    /**
     * Get formeActivite
     *
     * @return array
     */
    public function getFormeActivite()
    {
        return $this->formeActivite;
    }

    /**
     * Set formeJuridique
     *
     * @param array $formeJuridique
     *
     * @return TacheLegale
     */
    public function setFormeJuridique($formeJuridique)
    {
        $this->formeJuridique = $formeJuridique;

        return $this;
    }

    /**
     * Get formeJuridique
     *
     * @return array
     */
    public function getFormeJuridique()
    {
        return $this->formeJuridique;
    }

    /**
     * Set dateCloture
     *
     * @param array $dateCloture
     *
     * @return TacheLegale
     */
    public function setDateCloture($dateCloture)
    {
        $this->dateCloture = $dateCloture;

        return $this;
    }

    /**
     * Get dateCloture
     *
     * @return array
     */
    public function getDateCloture()
    {
        return $this->dateCloture;
    }

    /**
     * Set evenementDeclencheur
     *
     * @param string $evenementDeclencheur
     *
     * @return TacheLegale
     */
    public function setEvenementDeclencheur($evenementDeclencheur)
    {
        $this->evenementDeclencheur = $evenementDeclencheur;

        return $this;
    }

    /**
     * Get evenementDeclencheur
     *
     * @return string
     */
    public function getEvenementDeclencheur()
    {
        return $this->evenementDeclencheur;
    }

    /**
     * Set periodeDeclaration
     *
     * @param integer $periodeDeclaration
     *
     * @return TacheLegale
     */
    public function setPeriodeDeclaration($periodeDeclaration)
    {
        $this->periodeDeclaration = $periodeDeclaration;

        return $this;
    }

    /**
     * Get periodeDeclaration
     *
     * @return integer
     */
    public function getPeriodeDeclaration()
    {
        return $this->periodeDeclaration;
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
     * Constructor
     */
    public function __construct()
    {
        $this->actions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add action
     *
     * @param \AppBundle\Entity\TacheLegaleAction $action
     *
     * @return TacheLegale
     */
    public function addAction(\AppBundle\Entity\TacheLegaleAction $action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * Remove action
     *
     * @param \AppBundle\Entity\TacheLegaleAction $action
     */
    public function removeAction(\AppBundle\Entity\TacheLegaleAction $action)
    {
        $this->actions->removeElement($action);
    }

    /**
     * Get actions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActions()
    {
        return $this->actions;
    }
}
