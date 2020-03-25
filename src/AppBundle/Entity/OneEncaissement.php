<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OneEncaissement
 *
 * @ORM\Table(name="one_encaissement", indexes={@ORM\Index(name="fk_encaissement_one_type_encaissement1_idx", columns={"one_type_encaissement_id"}), @ORM\Index(name="fk_encaissement_one_client_prospect1_idx", columns={"one_client_prospect_id"}), @ORM\Index(name="fk_encaissement_one_reglement1_idx", columns={"one_reglement_id"})})
 * @ORM\Entity
 */
class OneEncaissement
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_encaissement", type="date", nullable=false)
     */
    private $dateEncaissement;

    /**
     * @var string
     *
     * @ORM\Column(name="id_transaction", type="string", length=50, nullable=true)
     */
    private $idTransaction;

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
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\OneTypeEncaissement
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OneTypeEncaissement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="one_type_encaissement_id", referencedColumnName="id")
     * })
     */
    private $oneTypeEncaissement;

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
     * @var \AppBundle\Entity\OneClientProspect
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OneClientProspect")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="one_client_prospect_id", referencedColumnName="id")
     * })
     */
    private $oneClientProspect;



    /**
     * Set dateEncaissement
     *
     * @param \DateTime $dateEncaissement
     *
     * @return OneEncaissement
     */
    public function setDateEncaissement($dateEncaissement)
    {
        $this->dateEncaissement = $dateEncaissement;

        return $this;
    }

    /**
     * Get dateEncaissement
     *
     * @return \DateTime
     */
    public function getDateEncaissement()
    {
        return $this->dateEncaissement;
    }

    /**
     * Set idTransaction
     *
     * @param string $idTransaction
     *
     * @return OneEncaissement
     */
    public function setIdTransaction($idTransaction)
    {
        $this->idTransaction = $idTransaction;

        return $this;
    }

    /**
     * Get idTransaction
     *
     * @return string
     */
    public function getIdTransaction()
    {
        return $this->idTransaction;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return OneEncaissement
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
     * @return OneEncaissement
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set oneTypeEncaissement
     *
     * @param \AppBundle\Entity\OneTypeEncaissement $oneTypeEncaissement
     *
     * @return OneEncaissement
     */
    public function setOneTypeEncaissement(\AppBundle\Entity\OneTypeEncaissement $oneTypeEncaissement = null)
    {
        $this->oneTypeEncaissement = $oneTypeEncaissement;

        return $this;
    }

    /**
     * Get oneTypeEncaissement
     *
     * @return \AppBundle\Entity\OneTypeEncaissement
     */
    public function getOneTypeEncaissement()
    {
        return $this->oneTypeEncaissement;
    }

    /**
     * Set oneReglement
     *
     * @param \AppBundle\Entity\OneReglement $oneReglement
     *
     * @return OneEncaissement
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
     * Set oneClientProspect
     *
     * @param \AppBundle\Entity\OneClientProspect $oneClientProspect
     *
     * @return OneEncaissement
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
