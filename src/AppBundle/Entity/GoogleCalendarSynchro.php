<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GoogleCalendarSynchro
 *
 * @ORM\Table(name="google_calendar_synchro", indexes={@ORM\Index(name="fk_google_calendar_synchro_client1_idx", columns={"client_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GoogleCalendarSynchroRepository")
 */
class GoogleCalendarSynchro
{
    /**
     * @var string
     *
     * @ORM\Column(name="identifiant", type="string", length=255, nullable=false)
     */
    private $identifiant;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="original_title", type="string", length=255, nullable=false)
     */
    private $originalTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="datetime", nullable=false)
     */
    private $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="datetime", nullable=false)
     */
    private $end;

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
     * Set identifiant
     *
     * @param string $identifiant
     *
     * @return GoogleCalendarSynchro
     */
    public function setIdentifiant($identifiant)
    {
        $this->identifiant = $identifiant;

        return $this;
    }

    /**
     * Get identifiant
     *
     * @return string
     */
    public function getIdentifiant()
    {
        return $this->identifiant;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return GoogleCalendarSynchro
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set originalTitle
     *
     * @param string $originalTitle
     *
     * @return GoogleCalendarSynchro
     */
    public function setOriginalTitle($originalTitle)
    {
        $this->originalTitle = $originalTitle;

        return $this;
    }

    /**
     * Get orignalTitle
     *
     * @return string
     */
    public function getOriginalTitle()
    {
        return $this->originalTitle;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return GoogleCalendarSynchro
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
     * Get Start
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set Start
     *
     * @param \DateTime $start
     * @return GoogleCalendarSynchro
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * Get End
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set End
     *
     * @param \DateTime $end
     * @return GoogleCalendarSynchro
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
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
     * @return GoogleCalendarSynchro
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
