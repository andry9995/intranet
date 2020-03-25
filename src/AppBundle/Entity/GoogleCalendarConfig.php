<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GoogleCalendarConfig
 *
 * @ORM\Table(name="google_calendar_config", indexes={@ORM\Index(name="fk_google_calendar_client1_idx", columns={"client_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GoogleCalendarConfigRepository")
 */
class GoogleCalendarConfig
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
     * @ORM\Column(name="color", type="string", length=45, nullable=true)
     */
    private $color = '#3cb08c';

    /**
     * @var string
     *
     * @ORM\Column(name="text_color", type="string", length=45, nullable=true)
     */
    private $textColor = '#fff';

    /** @var boolean
     *
     * @ORM\Column(name="send_to_google", type="boolean", nullable=false)
     */
    private $sendToGoogle = '0';

    /**
     * @return bool
     */
    public function isSendToGoogle()
    {
        return $this->sendToGoogle;
    }

    /**
     * @param bool $sendToGoogle
     * @return GoogleCalendarConfig
     */
    public function setSendToGoogle($sendToGoogle)
    {
        $this->sendToGoogle = $sendToGoogle;
        return $this;
    }

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
     * Set url
     *
     * @param string $identifiant
     *
     * @return GoogleCalendar
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
     * Set color
     *
     * @param string $color
     *
     * @return GoogleCalendar
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set textColor
     *
     * @param string $textColor
     *
     * @return GoogleCalendar
     */
    public function setTextColor($textColor)
    {
        $this->textColor = $textColor;

        return $this;
    }

    /**
     * Get textColor
     *
     * @return string
     */
    public function getTextColor()
    {
        return $this->textColor;
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
     * @return GoogleCalendar
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
