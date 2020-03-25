<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OneDevis
 *
 * @ORM\Table(name="one_devis", indexes={@ORM\Index(name="fk_devis_one_client_prospect1_idx", columns={"one_client_prospect_id"}), @ORM\Index(name="fk_devis_one_contact_client1_idx", columns={"one_contact_client_id"}), @ORM\Index(name="fk_devis_one_reglement1_idx", columns={"one_reglement_id"})})
 * @ORM\Entity
 */
class OneDevis
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_devis", type="date", nullable=false)
     */
    private $dateDevis;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fin_validite", type="date", nullable=false)
     */
    private $finValidite;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\OneReglement
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OneReglement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="one_reglement_id", referencedColumnName="id")
     * })
     */
    private $oneReglement;

    /**
     * @var \AppBundle\Entity\OneContactClient
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OneContactClient")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="one_contact_client_id", referencedColumnName="id")
     * })
     */
    private $oneContactClient;

    /**
     * @var \AppBundle\Entity\OneClientProspect
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OneClientProspect")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="one_client_prospect_id", referencedColumnName="id")
     * })
     */
    private $oneClientProspect;



    /**
     * Set dateDevis
     *
     * @param \DateTime $dateDevis
     *
     * @return OneDevis
     */
    public function setDateDevis($dateDevis)
    {
        $this->dateDevis = $dateDevis;

        return $this;
    }

    /**
     * Get dateDevis
     *
     * @return \DateTime
     */
    public function getDateDevis()
    {
        return $this->dateDevis;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return OneDevis
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
     * Set finValidite
     *
     * @param \DateTime $finValidite
     *
     * @return OneDevis
     */
    public function setFinValidite($finValidite)
    {
        $this->finValidite = $finValidite;

        return $this;
    }

    /**
     * Get finValidite
     *
     * @return \DateTime
     */
    public function getFinValidite()
    {
        return $this->finValidite;
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
     * Set oneReglement
     *
     * @param \AppBundle\Entity\OneReglement $oneReglement
     *
     * @return OneDevis
     */
    public function setOneReglement(\AppBundle\Entity\OneReglement $oneReglement = null)
    {
        $this->oneReglement = $oneReglement;

        return $this;
    }

    /**
     * Get oneReglement
     *
     * @return \AppBundle\Entity\OneReglement
     */
    public function getOneReglement()
    {
        return $this->oneReglement;
    }

    /**
     * Set oneContactClient
     *
     * @param \AppBundle\Entity\OneContactClient $oneContactClient
     *
     * @return OneDevis
     */
    public function setOneContactClient(\AppBundle\Entity\OneContactClient $oneContactClient = null)
    {
        $this->oneContactClient = $oneContactClient;

        return $this;
    }

    /**
     * Get oneContactClient
     *
     * @return \AppBundle\Entity\OneContactClient
     */
    public function getOneContactClient()
    {
        return $this->oneContactClient;
    }

    /**
     * Set oneClientProspect
     *
     * @param \AppBundle\Entity\OneClientProspect $oneClientProspect
     *
     * @return OneDevis
     */
    public function setOneClientProspect(\AppBundle\Entity\OneClientProspect $oneClientProspect = null)
    {
        $this->oneClientProspect = $oneClientProspect;

        return $this;
    }

    /**
     * Get oneClientProspect
     *
     * @return \AppBundle\Entity\OneClientProspect
     */
    public function getOneClientProspect()
    {
        return $this->oneClientProspect;
    }
}
