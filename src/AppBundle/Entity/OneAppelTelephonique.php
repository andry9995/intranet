<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OneAppelTelephonique
 *
 * @ORM\Table(name="one_appel_telephonique", indexes={@ORM\Index(name="fk_one_appel_telephonique_one_contact_client1_idx", columns={"one_contact_client_id"}), @ORM\Index(name="fk_one_appel_telephonique_opportunite1_idx", columns={"opportunite_id"}), @ORM\Index(name="fk_one_appel_telephonique_one_client_prospect1_idx", columns={"one_client_prospect_id"}), @ORM\Index(name="fk_one_appel_telephonique_one_qualification1_idx", columns={"one_qualification_id"})})
 * @ORM\Entity
 */
class OneAppelTelephonique
{
    /**
     * @var string
     *
     * @ORM\Column(name="sujet", type="string", length=50, nullable=false)
     */
    private $sujet;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", length=65535, nullable=true)
     */
    private $note;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="echeance", type="date", nullable=false)
     */
    private $echeance;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\OneOpportunite
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OneOpportunite")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="opportunite_id", referencedColumnName="id")
     * })
     */
    private $opportunite;

    /**
     * @var \AppBundle\Entity\OneQualification
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OneQualification")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="one_qualification_id", referencedColumnName="id")
     * })
     */
    private $oneQualification;

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
     * Set sujet
     *
     * @param string $sujet
     *
     * @return OneAppelTelephonique
     */
    public function setSujet($sujet)
    {
        $this->sujet = $sujet;

        return $this;
    }

    /**
     * Get sujet
     *
     * @return string
     */
    public function getSujet()
    {
        return $this->sujet;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return OneAppelTelephonique
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return OneAppelTelephonique
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
     * Set echeance
     *
     * @param \DateTime $echeance
     *
     * @return OneAppelTelephonique
     */
    public function setEcheance($echeance)
    {
        $this->echeance = $echeance;

        return $this;
    }

    /**
     * Get echeance
     *
     * @return \DateTime
     */
    public function getEcheance()
    {
        return $this->echeance;
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
     * Set opportunite
     *
     * @param \AppBundle\Entity\OneOpportunite $opportunite
     *
     * @return OneAppelTelephonique
     */
    public function setOpportunite(\AppBundle\Entity\OneOpportunite $opportunite = null)
    {
        $this->opportunite = $opportunite;

        return $this;
    }

    /**
     * Get opportunite
     *
     * @return \AppBundle\Entity\OneOpportunite
     */
    public function getOpportunite()
    {
        return $this->opportunite;
    }

    /**
     * Set oneQualification
     *
     * @param \AppBundle\Entity\OneQualification $oneQualification
     *
     * @return OneAppelTelephonique
     */
    public function setOneQualification(\AppBundle\Entity\OneQualification $oneQualification = null)
    {
        $this->oneQualification = $oneQualification;

        return $this;
    }

    /**
     * Get oneQualification
     *
     * @return \AppBundle\Entity\OneQualification
     */
    public function getOneQualification()
    {
        return $this->oneQualification;
    }

    /**
     * Set oneContactClient
     *
     * @param \AppBundle\Entity\OneContactClient $oneContactClient
     *
     * @return OneAppelTelephonique
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
     * @return OneAppelTelephonique
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
