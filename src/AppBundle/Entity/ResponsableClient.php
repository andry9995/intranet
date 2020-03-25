<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ResponsableClient
 *
 * @ORM\Table(name="responsable_client", indexes={@ORM\Index(name="fk_responsable_client_client1_idx", columns={"client"}), @ORM\Index(name="fk_responsable_client_operateur_idx", columns={"responsable"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ResponsableClientRepository")
 */
class ResponsableClient
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
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="responsable", referencedColumnName="id")
     * })
     */
    private $responsable;

    /**
     * @var \AppBundle\Entity\Client
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="client", referencedColumnName="id")
     * })
     */
    private $client;



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
     * Set responsable
     *
     * @param \AppBundle\Entity\Operateur $responsable
     *
     * @return ResponsableClient
     */
    public function setResponsable(\AppBundle\Entity\Operateur $responsable = null)
    {
        $this->responsable = $responsable;

        return $this;
    }

    /**
     * Get responsable
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getResponsable()
    {
        return $this->responsable;
    }

    /**
     * Set client
     *
     * @param \AppBundle\Entity\Client $client
     *
     * @return ResponsableClient
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
