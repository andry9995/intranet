<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OneVente
 *
 * @ORM\Table(name="one_vente", indexes={@ORM\Index(name="fk_vente_one_client_prospect1_idx", columns={"one_client_prospect_id"}), @ORM\Index(name="fk_vente_one_contact_client1_idx", columns={"contact"}), @ORM\Index(name="fk_vente_one_reglement1_idx", columns={"one_reglement_id"}), @ORM\Index(name="fk_vente_one_contact_client2_idx", columns={"contact_livraison"})})
 * @ORM\Entity
 */
class OneVente
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_facture", type="date", nullable=false)
     */
    private $dateFacture;

    /**
     * @var integer
     *
     * @ORM\Column(name="status_facture", type="integer", nullable=true)
     */
    private $statusFacture;

    /**
     * @var integer
     *
     * @ORM\Column(name="status_bon_commande", type="integer", nullable=true)
     */
    private $statusBonCommande;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", length=65535, nullable=true)
     */
    private $note;

    /**
     * @var string
     *
     * @ORM\Column(name="fichier", type="text", length=65535, nullable=true)
     */
    private $fichier;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer", nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_client", type="string", length=50, nullable=true)
     */
    private $refClient;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_expedition", type="date", nullable=true)
     */
    private $dateExpedition;

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
     *   @ORM\JoinColumn(name="contact_livraison", referencedColumnName="id")
     * })
     */
    private $contactLivraison;

    /**
     * @var \AppBundle\Entity\OneContactClient
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OneContactClient")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contact", referencedColumnName="id")
     * })
     */
    private $contact;

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
     * Set dateFacture
     *
     * @param \DateTime $dateFacture
     *
     * @return OneVente
     */
    public function setDateFacture($dateFacture)
    {
        $this->dateFacture = $dateFacture;

        return $this;
    }

    /**
     * Get dateFacture
     *
     * @return \DateTime
     */
    public function getDateFacture()
    {
        return $this->dateFacture;
    }

    /**
     * Set statusFacture
     *
     * @param integer $statusFacture
     *
     * @return OneVente
     */
    public function setStatusFacture($statusFacture)
    {
        $this->statusFacture = $statusFacture;

        return $this;
    }

    /**
     * Get statusFacture
     *
     * @return integer
     */
    public function getStatusFacture()
    {
        return $this->statusFacture;
    }

    /**
     * Set statusBonCommande
     *
     * @param integer $statusBonCommande
     *
     * @return OneVente
     */
    public function setStatusBonCommande($statusBonCommande)
    {
        $this->statusBonCommande = $statusBonCommande;

        return $this;
    }

    /**
     * Get statusBonCommande
     *
     * @return integer
     */
    public function getStatusBonCommande()
    {
        return $this->statusBonCommande;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return OneVente
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
     * Set fichier
     *
     * @param string $fichier
     *
     * @return OneVente
     */
    public function setFichier($fichier)
    {
        $this->fichier = $fichier;

        return $this;
    }

    /**
     * Get fichier
     *
     * @return string
     */
    public function getFichier()
    {
        return $this->fichier;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return OneVente
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set refClient
     *
     * @param string $refClient
     *
     * @return OneVente
     */
    public function setRefClient($refClient)
    {
        $this->refClient = $refClient;

        return $this;
    }

    /**
     * Get refClient
     *
     * @return string
     */
    public function getRefClient()
    {
        return $this->refClient;
    }

    /**
     * Set dateExpedition
     *
     * @param \DateTime $dateExpedition
     *
     * @return OneVente
     */
    public function setDateExpedition($dateExpedition)
    {
        $this->dateExpedition = $dateExpedition;

        return $this;
    }

    /**
     * Get dateExpedition
     *
     * @return \DateTime
     */
    public function getDateExpedition()
    {
        return $this->dateExpedition;
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
     * @return OneVente
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
     * Set contactLivraison
     *
     * @param \AppBundle\Entity\OneContactClient $contactLivraison
     *
     * @return OneVente
     */
    public function setContactLivraison(\AppBundle\Entity\OneContactClient $contactLivraison = null)
    {
        $this->contactLivraison = $contactLivraison;

        return $this;
    }

    /**
     * Get contactLivraison
     *
     * @return \AppBundle\Entity\OneContactClient
     */
    public function getContactLivraison()
    {
        return $this->contactLivraison;
    }

    /**
     * Set contact
     *
     * @param \AppBundle\Entity\OneContactClient $contact
     *
     * @return OneVente
     */
    public function setContact(\AppBundle\Entity\OneContactClient $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \AppBundle\Entity\OneContactClient
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set oneClientProspect
     *
     * @param \AppBundle\Entity\OneClientProspect $oneClientProspect
     *
     * @return OneVente
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
