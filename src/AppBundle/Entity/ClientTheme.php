<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClientTheme
 *
 * @ORM\Table(name="client_theme", uniqueConstraints={@ORM\UniqueConstraint(name="client_UNIQUE", columns={"client_id"})}, indexes={@ORM\Index(name="fk_client_theme_client1_idx", columns={"client_id"})})
 * @ORM\Entity
 */
class ClientTheme
{
    /**
     * @var string
     *
     * @ORM\Column(name="theme", type="string", length=50, nullable=true)
     */
    private $theme;

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
     * Set theme
     *
     * @param string $theme
     *
     * @return ClientTheme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
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
     * @return ClientTheme
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
