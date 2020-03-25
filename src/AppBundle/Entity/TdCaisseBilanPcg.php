<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TdCaisseBilanPcg
 *
 * @ORM\Table(name="td_caisse_bilan_pcg", indexes={@ORM\Index(name="fk_td_caisse_bilan_pcg1_idx", columns={"pcg_id"})})
 * @ORM\Entity
 */
class TdCaisseBilanPcg
{
    /**
     * @var string
     *
     * @ORM\Column(name="compte", type="string", length=45, nullable=false)
     */
    private $compte;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * Set compte
     *
     * @param string $compte
     *
     * @return TdCaisseBilanPcg
     */
    public function setCompte($compte)
    {
        $this->compte = $compte;

        return $this;
    }

    /**
     * Get compte
     *
     * @return string
     */
    public function getCompte()
    {
        return $this->compte;
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
     * Set pcg
     *
     * @param \AppBundle\Entity\Pcg $pcg
     *
     * @return TdCaisseBilanPcg
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
