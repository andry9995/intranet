<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TdNdfTvaPcg
 *
 * @ORM\Table(name="td_ndf_tva_pcg", indexes={@ORM\Index(name="fk_td_ndf_tva_pcg_type_frais1_idx", columns={"type_frais_id"}), @ORM\Index(name="fk_td_ndf_tva_pcg_sousnature1_idx", columns={"sousnature_id"}), @ORM\Index(name="fk_td_ndf_tva_pcg_pcg1_idx", columns={"pcg_id"})})
 * @ORM\Entity
 */
class TdNdfTvaPcg
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
     * @var \AppBundle\Entity\TypeFrais
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TypeFrais")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_frais_id", referencedColumnName="id")
     * })
     */
    private $typeFrais;

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
     * Set compte
     *
     * @param string $compte
     *
     * @return TdNdfTvaPcg
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
     * Set typeFrais
     *
     * @param \AppBundle\Entity\TypeFrais $typeFrais
     *
     * @return TdNdfTvaPcg
     */
    public function setTypeFrais(\AppBundle\Entity\TypeFrais $typeFrais = null)
    {
        $this->typeFrais = $typeFrais;

        return $this;
    }

    /**
     * Get typeFrais
     *
     * @return \AppBundle\Entity\TypeFrais
     */
    public function getTypeFrais()
    {
        return $this->typeFrais;
    }

    /**
     * Set sousnature
     *
     * @param \AppBundle\Entity\Sousnature $sousnature
     *
     * @return TdNdfTvaPcg
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
     * @return TdNdfTvaPcg
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
