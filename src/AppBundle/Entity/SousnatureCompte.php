<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SousnatureCompte
 *
 * @ORM\Table(name="sousnature_compte", indexes={@ORM\Index(name="fk_ssnature_compte_ssnature_id_idx", columns={"sousnature_id"}), @ORM\Index(name="fk_ssnature_compte_pcg_id_idx", columns={"pcg_id"})})
 * @ORM\Entity
 */
class SousnatureCompte
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
     * @var \AppBundle\Entity\Sousnature
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Sousnature")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sousnature_id", referencedColumnName="id")
     * })
     */
    private $sousnature;

    /**
     * @var \AppBundle\Entity\Pcg
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcg")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pcg_id", referencedColumnName="id")
     * })
     */
    private $pcg;



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
     * Set sousnature
     *
     * @param \AppBundle\Entity\Sousnature $sousnature
     *
     * @return SousnatureCompte
     */
    public function setSousnature(\AppBundle\Entity\Sousnature $sousnature = null)
    {
        $this->sousnature = $sousnature;

        return $this;
    }

    /**
     * Get sousnature
     *
     * @return \AppBundle\Entity\Sousnature
     */
    public function getSousnature()
    {
        return $this->sousnature;
    }

    /**
     * Set pcg
     *
     * @param \AppBundle\Entity\Pcg $pcg
     *
     * @return SousnatureCompte
     */
    public function setPcg(\AppBundle\Entity\Pcg $pcg = null)
    {
        $this->pcg = $pcg;

        return $this;
    }

    /**
     * Get pcg
     *
     * @return \AppBundle\Entity\Pcg
     */
    public function getPcg()
    {
        return $this->pcg;
    }
}
