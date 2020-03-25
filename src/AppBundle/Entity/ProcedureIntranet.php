<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProcedureIntranet
 *
 * @ORM\Table(name="procedure_intranet", indexes={@ORM\Index(name="fk_procedure_organisation_id_idx", columns={"organisation_id"}), @ORM\Index(name="fk_procedure_unite_comptage_idx", columns={"unite_comptage_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProcedureIntranetRepository")
 */
class ProcedureIntranet
{
    /**
     * @var string
     *
     * @ORM\Column(name="numero", type="string", length=20, nullable=false)
     */
    private $numero;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=100, nullable=false)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var float
     *
     * @ORM\Column(name="duree", type="float", precision=10, scale=0, nullable=true)
     */
    private $duree = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\UniteComptage
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\UniteComptage")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="unite_comptage_id", referencedColumnName="id")
     * })
     */
    private $uniteComptage;

    /**
     * @var array
     * @ORM\Column(name="suivant", type="simple_array", nullable=true)
     */
    private $suivant;

    /**
     * @var array
     * @ORM\Column(name="precedent", type="simple_array", nullable=true)
     */
    private $precedent;

    /**
     * @var \AppBundle\Entity\Organisation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Organisation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     * })
     */
    private $organisation;

    /**
     * Set numero
     *
     * @param string $numero
     *
     * @return ProcedureIntranet
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return string
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return ProcedureIntranet
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
     * Set description
     *
     * @param string $description
     *
     * @return ProcedureIntranet
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
     * Set duree
     *
     * @param float $duree
     *
     * @return ProcedureIntranet
     */
    public function setDuree($duree)
    {
        $this->duree = $duree;

        return $this;
    }

    /**
     * Get duree
     *
     * @return float
     */
    public function getDuree()
    {
        return $this->duree;
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
     * Set suivant
     *
     * @param array $suivant
     *
     * @return ProcedureIntranet
     */
    public function setSuivant($suivant)
    {
        $this->suivant = $suivant;

        return $this;
    }

    /**
     * Get suivant
     *
     * @return array
     */
    public function getSuivant()
    {
        return $this->suivant;
    }

    /**
     * Set precedent
     *
     * @param array $precedent
     *
     * @return ProcedureIntranet
     */
    public function setPrecedent($precedent)
    {
        $this->precedent = $precedent;

        return $this;
    }

    /**
     * Get precedent
     *
     * @return array
     */
    public function getPrecedent()
    {
        return $this->precedent;
    }

    /**
     * Set uniteComptage
     *
     * @param \AppBundle\Entity\UniteComptage $uniteComptage
     *
     * @return ProcedureIntranet
     */
    public function setUniteComptage(\AppBundle\Entity\UniteComptage $uniteComptage = null)
    {
        $this->uniteComptage = $uniteComptage;

        return $this;
    }

    /**
     * Get uniteComptage
     *
     * @return \AppBundle\Entity\UniteComptage
     */
    public function getUniteComptage()
    {
        return $this->uniteComptage;
    }

    /**
     * Set organisation
     *
     * @param \AppBundle\Entity\Organisation $organisation
     *
     * @return ProcedureIntranet
     */
    public function setOrganisation(\AppBundle\Entity\Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get organisation
     *
     * @return \AppBundle\Entity\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }
}
