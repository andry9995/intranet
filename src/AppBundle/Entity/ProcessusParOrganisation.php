<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProcessusParOrganisation
 *
 * @ORM\Table(name="processus_par_organisation", indexes={@ORM\Index(name="fk_proorg_processid_idx", columns={"processus_id"}), @ORM\Index(name="fk_proorg_orgid_idx", columns={"organisation_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProcessusParOrganisationRepository")
 */
class ProcessusParOrganisation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Processus
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Processus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="processus_id", referencedColumnName="id")
     * })
     */
    private $processus;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set processus
     *
     * @param \AppBundle\Entity\Processus $processus
     *
     * @return ProcessusParOrganisation
     */
    public function setProcessus(\AppBundle\Entity\Processus $processus = null)
    {
        $this->processus = $processus;

        return $this;
    }

    /**
     * Get processus
     *
     * @return \AppBundle\Entity\Processus
     */
    public function getProcessus()
    {
        return $this->processus;
    }

    /**
     * Set organisation
     *
     * @param \AppBundle\Entity\Organisation $organisation
     *
     * @return ProcessusParOrganisation
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
