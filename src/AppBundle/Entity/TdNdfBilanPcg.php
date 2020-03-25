<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TdNdfBilanPcg
 *
 * @ORM\Table(name="td_ndf_bilan_pcg", indexes={@ORM\Index(name="fk_td_ndf_bilan_pcg_pcg1_idx", columns={"pcg"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TdNdfBilanPcgRepository")
 */
class TdNdfBilanPcg
{
    /**
     * @var integer
     *
     * @ORM\Column(name="type_compte", type="integer", nullable=true)
     */
    private $typeCompte;

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
     *   @ORM\JoinColumn(name="pcg", referencedColumnName="id")
     * })
     */
    private $pcg;



    /**
     * Set typeCompte
     *
     * @param integer $typeCompte
     *
     * @return TdNdfBilanPcg
     */
    public function setTypeCompte($typeCompte)
    {
        $this->typeCompte = $typeCompte;

        return $this;
    }

    /**
     * Get typeCompte
     *
     * @return integer
     */
    public function getTypeCompte()
    {
        return $this->typeCompte;
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
     * @return TdNdfBilanPcg
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
