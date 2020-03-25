<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Message
 *
 * @ORM\Table(name="message", indexes={@ORM\Index(name="fk_message_idexp_idx", columns={"id_expediteur"}), @ORM\Index(name="fk_message_iddest_idx", columns={"id_destinataire"}), @ORM\Index(name="fk_message_idpanier_idx", columns={"id_panier"})})
 * @ORM\Entity
 */
class Message
{
    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=250, nullable=false)
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=45, nullable=true)
     */
    private $titre;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_exp", type="datetime", nullable=true)
     */
    private $dateExp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_dest", type="datetime", nullable=true)
     */
    private $dateDest;

    /**
     * @var string
     *
     * @ORM\Column(name="etape", type="string", length=50, nullable=true)
     */
    private $etape;

    /**
     * @var string
     *
     * @ORM\Column(name="reponse", type="string", length=250, nullable=true)
     */
    private $reponse;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Panier
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Panier")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_panier", referencedColumnName="id")
     * })
     */
    private $idPanier;

    /**
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_expediteur", referencedColumnName="id")
     * })
     */
    private $idExpediteur;

    /**
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_destinataire", referencedColumnName="id")
     * })
     */
    private $idDestinataire;



    /**
     * Set message
     *
     * @param string $message
     *
     * @return Message
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set titre
     *
     * @param string $titre
     *
     * @return Message
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Message
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
     * Set dateExp
     *
     * @param \DateTime $dateExp
     *
     * @return Message
     */
    public function setDateExp($dateExp)
    {
        $this->dateExp = $dateExp;

        return $this;
    }

    /**
     * Get dateExp
     *
     * @return \DateTime
     */
    public function getDateExp()
    {
        return $this->dateExp;
    }

    /**
     * Set dateDest
     *
     * @param \DateTime $dateDest
     *
     * @return Message
     */
    public function setDateDest($dateDest)
    {
        $this->dateDest = $dateDest;

        return $this;
    }

    /**
     * Get dateDest
     *
     * @return \DateTime
     */
    public function getDateDest()
    {
        return $this->dateDest;
    }

    /**
     * Set etape
     *
     * @param string $etape
     *
     * @return Message
     */
    public function setEtape($etape)
    {
        $this->etape = $etape;

        return $this;
    }

    /**
     * Get etape
     *
     * @return string
     */
    public function getEtape()
    {
        return $this->etape;
    }

    /**
     * Set reponse
     *
     * @param string $reponse
     *
     * @return Message
     */
    public function setReponse($reponse)
    {
        $this->reponse = $reponse;

        return $this;
    }

    /**
     * Get reponse
     *
     * @return string
     */
    public function getReponse()
    {
        return $this->reponse;
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
     * Set idPanier
     *
     * @param \AppBundle\Entity\Panier $idPanier
     *
     * @return Message
     */
    public function setIdPanier(\AppBundle\Entity\Panier $idPanier = null)
    {
        $this->idPanier = $idPanier;

        return $this;
    }

    /**
     * Get idPanier
     *
     * @return \AppBundle\Entity\Panier
     */
    public function getIdPanier()
    {
        return $this->idPanier;
    }

    /**
     * Set idExpediteur
     *
     * @param \AppBundle\Entity\Operateur $idExpediteur
     *
     * @return Message
     */
    public function setIdExpediteur(\AppBundle\Entity\Operateur $idExpediteur = null)
    {
        $this->idExpediteur = $idExpediteur;

        return $this;
    }

    /**
     * Get idExpediteur
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getIdExpediteur()
    {
        return $this->idExpediteur;
    }

    /**
     * Set idDestinataire
     *
     * @param \AppBundle\Entity\Operateur $idDestinataire
     *
     * @return Message
     */
    public function setIdDestinataire(\AppBundle\Entity\Operateur $idDestinataire = null)
    {
        $this->idDestinataire = $idDestinataire;

        return $this;
    }

    /**
     * Get idDestinataire
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getIdDestinataire()
    {
        return $this->idDestinataire;
    }
}
