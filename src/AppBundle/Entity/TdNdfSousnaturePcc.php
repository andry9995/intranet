<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TdNdfSousnaturePcc
 *
 * @ORM\Table(name="td_ndf_sousnature_pcc", indexes={@ORM\Index(name="fk_td_ndf_sousnature_pcc_sousnature1_idx", columns={"sousnature_id"}), @ORM\Index(name="fk_td_ndf_sousnature_pcc1_idx", columns={"pcc_resultat"}), @ORM\Index(name="fk_td_ndf_sousnature_pcc2_idx", columns={"pcc_tva"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TdNdfSousnaturePccRepository")
 */
class TdNdfSousnaturePcc
{
    /**
     * @var integer
     *
     * @ORM\Column(name="nb_participant", type="integer", nullable=true)
     */
    private $nbParticipant;

    /**
     * @var integer
     *
     * @ORM\Column(name="distance", type="integer", nullable=true)
     */
    private $distance;

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
     *   @ORM\JoinColumn(name="pcc_tva", referencedColumnName="id")
     * })
     */
    private $pccTva;

    /**
     * @var \AppBundle\Entity\Pcc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pcc_resultat", referencedColumnName="id")
     * })
     */
    private $pccResultat;

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
     * Set nbParticipant
     *
     * @param integer $nbParticipant
     *
     * @return TdNdfSousnaturePcc
     */
    public function setNbParticipant($nbParticipant)
    {
        $this->nbParticipant = $nbParticipant;

        return $this;
    }

    /**
     * Get nbParticipant
     *
     * @return integer
     */
    public function getNbParticipant()
    {
        return $this->nbParticipant;
    }

    /**
     * Set distance
     *
     * @param integer $distance
     *
     * @return TdNdfSousnaturePcc
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Get distance
     *
     * @return integer
     */
    public function getDistance()
    {
        return $this->distance;
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
     * Set pccTva
     *
     * @param \AppBundle\Entity\Pcc $pccTva
     *
     * @return TdNdfSousnaturePcc
     */
    public function setPccTva(\AppBundle\Entity\Pcc $pccTva = null)
    {
        $this->pccTva = $pccTva;

        return $this;
    }

    /**
     * Get pccTva
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getPccTva()
    {
        return $this->pccTva;
    }

    /**
     * Set pccResultat
     *
     * @param \AppBundle\Entity\Pcc $pccResultat
     *
     * @return TdNdfSousnaturePcc
     */
    public function setPccResultat(\AppBundle\Entity\Pcc $pccResultat = null)
    {
        $this->pccResultat = $pccResultat;

        return $this;
    }

    /**
     * Get pccResultat
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getPccResultat()
    {
        return $this->pccResultat;
    }

    /**
     * Set sousnature
     *
     * @param \AppBundle\Entity\Sousnature $sousnature
     *
     * @return TdNdfSousnaturePcc
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
}
