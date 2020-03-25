<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TdNdfTvaPcc
 *
 * @ORM\Table(name="td_ndf_tva_pcc", indexes={@ORM\Index(name="fk_td_ndf_tva_pcc_pcc1_idx", columns={"pcc_id"}), @ORM\Index(name="fk_td_ndf_tva_pcc_type_frais1_idx", columns={"type_frais_id"}), @ORM\Index(name="fk_td_ndf_tva_pcc_sousnature1_idx", columns={"sousnature_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TdNdfTvaPccRepository")
 */
class TdNdfTvaPcc
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
     * @var \AppBundle\Entity\Pcc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pcc_id", referencedColumnName="id")
     * })
     */
    private $pcc;

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
     * @var \AppBundle\Entity\TypeFrais
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TypeFrais")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_frais_id", referencedColumnName="id")
     * })
     */
    private $typeFrais;



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
     * Set pcc
     *
     * @param \AppBundle\Entity\Pcc $pcc
     *
     * @return TdNdfTvaPcc
     */
    public function setPcc(\AppBundle\Entity\Pcc $pcc = null)
    {
        $this->pcc = $pcc;

        return $this;
    }

    /**
     * Get pcc
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getPcc()
    {
        return $this->pcc;
    }

    /**
     * Set sousnature
     *
     * @param \AppBundle\Entity\Sousnature $sousnature
     *
     * @return TdNdfTvaPcc
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
     * Set typeFrais
     *
     * @param \AppBundle\Entity\TypeFrais $typeFrais
     *
     * @return TdNdfTvaPcc
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
}
