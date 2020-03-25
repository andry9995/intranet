<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TdTvaPcg
 *
 * @ORM\Table(name="td_tva_pcg", indexes={@ORM\Index(name="fk_td_tva_pcg1_idx", columns={"pcg_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TdTvaPcgRepository")
 */
class TdTvaPcg
{
    /**
     * @var integer
     *
     * @ORM\Column(name="type_caisse", type="integer", nullable=false)
     */
    private $typeCaisse = '0';

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
     * Set typeCaisse
     *
     * @param integer $typeCaisse
     *
     * @return TdTvaPcg
     */
    public function setTypeCaisse($typeCaisse)
    {
        $this->typeCaisse = $typeCaisse;

        return $this;
    }

    /**
     * Get typeCaisse
     *
     * @return integer
     */
    public function getTypeCaisse()
    {
        return $this->typeCaisse;
    }

    /**
     * Set compte
     *
     * @param string $compte
     *
     * @return TdTvaPcg
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
     * @return TdTvaPcg
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
