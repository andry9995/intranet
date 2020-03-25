<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ParamEmailEcheance
 *
 * @ORM\Table(name="param_email_echeance", indexes={@ORM\Index(name="fk_param_email_echeance_client1_idx", columns={"client_id"})})
 * @ORM\Entity
 */
class ParamEmailEcheance
{
    /**
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=150, nullable=false)
     */
    private $titre;

    /**
     * @var string
     *
     * @ORM\Column(name="contenu", type="text", length=65535, nullable=false)
     */
    private $contenu;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_echeance", type="date", nullable=false)
     */
    private $dateEcheance;

    /**
     * @var string
     *
     * @ORM\Column(name="jours_avant", type="string", length=45, nullable=false)
     */
    private $joursAvant = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="recurrent", type="integer", nullable=false)
     */
    private $recurrent = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="recurrence_mois", type="integer", nullable=true)
     */
    private $recurrenceMois;

    /**
     * @var integer
     *
     * @ORM\Column(name="recurrence_max", type="integer", nullable=true)
     */
    private $recurrenceMax = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Client
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     * })
     */
    private $client;



    /**
     * Set titre
     *
     * @param string $titre
     *
     * @return ParamEmailEcheance
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
     * Set contenu
     *
     * @param string $contenu
     *
     * @return ParamEmailEcheance
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get contenu
     *
     * @return string
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Set dateEcheance
     *
     * @param \DateTime $dateEcheance
     *
     * @return ParamEmailEcheance
     */
    public function setDateEcheance($dateEcheance)
    {
        $this->dateEcheance = $dateEcheance;

        return $this;
    }

    /**
     * Get dateEcheance
     *
     * @return \DateTime
     */
    public function getDateEcheance()
    {
        return $this->dateEcheance;
    }

    /**
     * Set joursAvant
     *
     * @param string $joursAvant
     *
     * @return ParamEmailEcheance
     */
    public function setJoursAvant($joursAvant)
    {
        $this->joursAvant = $joursAvant;

        return $this;
    }

    /**
     * Get joursAvant
     *
     * @return string
     */
    public function getJoursAvant()
    {
        return $this->joursAvant;
    }

    /**
     * Set recurrent
     *
     * @param integer $recurrent
     *
     * @return ParamEmailEcheance
     */
    public function setRecurrent($recurrent)
    {
        $this->recurrent = $recurrent;

        return $this;
    }

    /**
     * Get recurrent
     *
     * @return integer
     */
    public function getRecurrent()
    {
        return $this->recurrent;
    }

    /**
     * Set recurrenceMois
     *
     * @param integer $recurrenceMois
     *
     * @return ParamEmailEcheance
     */
    public function setRecurrenceMois($recurrenceMois)
    {
        $this->recurrenceMois = $recurrenceMois;

        return $this;
    }

    /**
     * Get recurrenceMois
     *
     * @return integer
     */
    public function getRecurrenceMois()
    {
        return $this->recurrenceMois;
    }

    /**
     * Set recurrenceMax
     *
     * @param integer $recurrenceMax
     *
     * @return ParamEmailEcheance
     */
    public function setRecurrenceMax($recurrenceMax)
    {
        $this->recurrenceMax = $recurrenceMax;

        return $this;
    }

    /**
     * Get recurrenceMax
     *
     * @return integer
     */
    public function getRecurrenceMax()
    {
        return $this->recurrenceMax;
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
     * Set client
     *
     * @param \AppBundle\Entity\Client $client
     *
     * @return ParamEmailEcheance
     */
    public function setClient(\AppBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \AppBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
