<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TachesGroupRegimeFiscal
 *
 * @ORM\Table(name="taches_group_regime_fiscal", indexes={@ORM\Index(name="fk_taches_group_regime_fiscal_regime_fiscal_idx", columns={"regime_fiscal_id"}), @ORM\Index(name="fk_taches_group_regime_fiscal_taches_group_idx", columns={"taches_group_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TachesGroupRegimeFiscalRepository")
 */
class TachesGroupRegimeFiscal
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
     * @var \AppBundle\Entity\TachesGroup
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TachesGroup")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="taches_group_id", referencedColumnName="id")
     * })
     */
    private $tachesGroup;

    /**
     * @var \AppBundle\Entity\RegimeFiscal
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\RegimeFiscal")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="regime_fiscal_id", referencedColumnName="id")
     * })
     */
    private $regimeFiscal;



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
     * Set tachesGroup
     *
     * @param \AppBundle\Entity\TachesGroup $tachesGroup
     *
     * @return TachesGroupRegimeFiscal
     */
    public function setTachesGroup(\AppBundle\Entity\TachesGroup $tachesGroup = null)
    {
        $this->tachesGroup = $tachesGroup;

        return $this;
    }

    /**
     * Get tachesGroup
     *
     * @return \AppBundle\Entity\TachesGroup
     */
    public function getTachesGroup()
    {
        return $this->tachesGroup;
    }

    /**
     * Set regimeFiscal
     *
     * @param \AppBundle\Entity\RegimeFiscal $regimeFiscal
     *
     * @return TachesGroupRegimeFiscal
     */
    public function setRegimeFiscal(\AppBundle\Entity\RegimeFiscal $regimeFiscal = null)
    {
        $this->regimeFiscal = $regimeFiscal;

        return $this;
    }

    /**
     * Get regimeFiscal
     *
     * @return \AppBundle\Entity\RegimeFiscal
     */
    public function getRegimeFiscal()
    {
        return $this->regimeFiscal;
    }
}
