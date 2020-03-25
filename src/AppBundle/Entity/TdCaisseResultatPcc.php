<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TdCaisseResultatPcc
 *
 * @ORM\Table(name="td_caisse_resultat_pcc", uniqueConstraints={@ORM\UniqueConstraint(name="unique", columns={"caisse_nature_id", "caisse_type_id"})}, indexes={@ORM\Index(name="td_caisse_resultat_pcc_1_idx", columns={"pcc_id"}), @ORM\Index(name="td_caisse_resultat_pcc_caisse_nature1_idx", columns={"caisse_nature_id"}), @ORM\Index(name="td_caisse_resultat_pcc_caisse_type1_idx", columns={"caisse_type_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TdCaisseResultatPccRepository")
 */
class TdCaisseResultatPcc
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
     * @var \AppBundle\Entity\CaisseType
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CaisseType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="caisse_type_id", referencedColumnName="id")
     * })
     */
    private $caisseType;

    /**
     * @var \AppBundle\Entity\CaisseNature
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CaisseNature")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="caisse_nature_id", referencedColumnName="id")
     * })
     */
    private $caisseNature;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set caisseType
     *
     * @param \AppBundle\Entity\CaisseType $caisseType
     *
     * @return TdCaisseResultatPcc
     */
    public function setCaisseType(\AppBundle\Entity\CaisseType $caisseType = null)
    {
        $this->caisseType = $caisseType;

        return $this;
    }

    /**
     * Get caisseType
     *
     * @return \AppBundle\Entity\CaisseType
     */
    public function getCaisseType()
    {
        return $this->caisseType;
    }

    /**
     * Set caisseNature
     *
     * @param \AppBundle\Entity\CaisseNature $caisseNature
     *
     * @return TdCaisseResultatPcc
     */
    public function setCaisseNature(\AppBundle\Entity\CaisseNature $caisseNature = null)
    {
        $this->caisseNature = $caisseNature;

        return $this;
    }

    /**
     * Get caisseNature
     *
     * @return \AppBundle\Entity\CaisseNature
     */
    public function getCaisseNature()
    {
        return $this->caisseNature;
    }

    /**
     * Set pcc
     *
     * @param \AppBundle\Entity\Pcc $pcc
     *
     * @return TdCaisseResultatPcc
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
}
