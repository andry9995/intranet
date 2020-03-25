<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Organisation
 *
 * @ORM\Table(name="organisation", indexes={@ORM\Index(name="fk_org_org_idx", columns={"organisation_id"}), @ORM\Index(name="fk_organisation_titre_org1_idx", columns={"organisation_niveau_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrganisationRepository")
 */
class Organisation
{
    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255, nullable=false)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="capacite", type="integer", nullable=false)
     */
    private $capacite = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * @var \AppBundle\Entity\OrganisationNiveau
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OrganisationNiveau")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisation_niveau_id", referencedColumnName="id")
     * })
     */
    private $organisationNiveau;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=45, nullable=true)
     */
    private $code;

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Organisation
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
     * @return Organisation
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
     * set capacite
     *
     * @param $capacite
     * @return $this
     */
    public function setCapacite($capacite)
    {
        $this->capacite = $capacite;
        return $this;
    }

    /**
     * Get capacite
     *
     * @return int
     */
    public function getCapacite()
    {
        return $this->capacite;
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
     * Set organisation
     *
     * @param \AppBundle\Entity\Organisation $organisation
     *
     * @return Organisation
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

    /**
     * Set organisationNiveau
     *
     * @param \AppBundle\Entity\OrganisationNiveau $organisationNiveau
     *
     * @return Organisation
     */
    public function setOrganisationNiveau(\AppBundle\Entity\OrganisationNiveau $organisationNiveau = null)
    {
        $this->organisationNiveau = $organisationNiveau;

        return $this;
    }

    /**
     * Get organisationNiveau
     *
     * @return \AppBundle\Entity\OrganisationNiveau
     */
    public function getOrganisationNiveau()
    {
        return $this->organisationNiveau;
    }


    /**
     * Set nom
     *
     * @param string $code
     *
     * @return Organisation
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
