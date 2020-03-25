<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FactRemiseApplique
 *
 * @ORM\Table(name="fact_remise_applique", indexes={@ORM\Index(name="fk_fact_remise_applique_client1_idx", columns={"client_id"}), @ORM\Index(name="fk_fact_remise_applique_remise_niveau1_idx", columns={"fact_remise_niveau_id"})})
 * @ORM\Entity
 */
class FactRemiseApplique
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
     * @var \AppBundle\Entity\FactRemiseNiveau
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\FactRemiseNiveau")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fact_remise_niveau_id", referencedColumnName="id")
     * })
     */
    private $factRemiseNiveau;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set factRemiseNiveau
     *
     * @param \AppBundle\Entity\FactRemiseNiveau $factRemiseNiveau
     *
     * @return FactRemiseApplique
     */
    public function setFactRemiseNiveau(\AppBundle\Entity\FactRemiseNiveau $factRemiseNiveau = null)
    {
        $this->factRemiseNiveau = $factRemiseNiveau;

        return $this;
    }

    /**
     * Get factRemiseNiveau
     *
     * @return \AppBundle\Entity\FactRemiseNiveau
     */
    public function getFactRemiseNiveau()
    {
        return $this->factRemiseNiveau;
    }

    /**
     * Set client
     *
     * @param \AppBundle\Entity\Client $client
     *
     * @return FactRemiseApplique
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
