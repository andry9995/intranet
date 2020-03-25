<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClientAFacturer
 *
 * @ORM\Table(name="client_a_facturer", indexes={@ORM\Index(name="fk_client_a_facutrer_client1_idx", columns={"client_id"})})
 * @ORM\Entity
 */
class ClientAFacturer
{
    /**
     * @var boolean
     *
     * @ORM\Column(name="contrat_signe", type="boolean", nullable=false)
     */
    private $contratSigne = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="avenant", type="boolean", nullable=false)
     */
    private $avenant = '0';

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
     * Set contratSigne
     *
     * @param boolean $contratSigne
     *
     * @return ClientAFacturer
     */
    public function setContratSigne($contratSigne)
    {
        $this->contratSigne = $contratSigne;

        return $this;
    }

    /**
     * Get contratSigne
     *
     * @return boolean
     */
    public function getContratSigne()
    {
        return $this->contratSigne;
    }

    /**
     * Set avenant
     *
     * @param boolean $avenant
     *
     * @return ClientAFacturer
     */
    public function setAvenant($avenant)
    {
        $this->avenant = $avenant;

        return $this;
    }

    /**
     * Get avenant
     *
     * @return boolean
     */
    public function getAvenant()
    {
        return $this->avenant;
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
     * @return ClientAFacturer
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
