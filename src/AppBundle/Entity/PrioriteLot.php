<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrioriteLot
 *
 * @ORM\Table(name="priorite_lot", indexes={@ORM\Index(name="fk_priorite_lot_lot1_idx", columns={"lot_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PrioriteLotRepository")
 */
class PrioriteLot
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="delai", type="datetime", nullable=true)
     */
    private $delai;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Lot
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Lot")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     * })
     */
    private $lot;



    /**
     * Set delai
     *
     * @param \DateTime $delai
     *
     * @return PrioriteLot
     */
    public function setDelai($delai)
    {
        $this->delai = $delai;

        return $this;
    }

    /**
     * Get delai
     *
     * @return \DateTime
     */
    public function getDelai()
    {
        return $this->delai;
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
     * Set lot
     *
     * @param \AppBundle\Entity\Lot $lot
     *
     * @return PrioriteLot
     */
    public function setLot(\AppBundle\Entity\Lot $lot = null)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * Get lot
     *
     * @return \AppBundle\Entity\Lot
     */
    public function getLot()
    {
        return $this->lot;
    }
}
