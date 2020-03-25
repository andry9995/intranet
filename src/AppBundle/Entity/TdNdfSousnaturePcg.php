<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TdNdfSousnaturePcg
 *
 * @ORM\Table(name="td_ndf_sousnature_pcg", indexes={@ORM\Index(name="fk_td_sousnature1_idx", columns={"sousnature_id"}), @ORM\Index(name="fk_td_pcg1_idx", columns={"pcg_resultat"}), @ORM\Index(name="fk_td_pcg2_idx", columns={"pcg_tva"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TdNdfSousnaturePcgRepository")
 */
class TdNdfSousnaturePcg
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
     *   @ORM\JoinColumn(name="pcg_tva", referencedColumnName="id")
     * })
     */
    private $pcgTva;

    /**
     * @var \AppBundle\Entity\Pcg
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcg")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pcg_resultat", referencedColumnName="id")
     * })
     */
    private $pcgResultat;



    /**
     * Set nbParticipant
     *
     * @param integer $nbParticipant
     *
     * @return TdNdfSousnaturePcg
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
     * @return TdNdfSousnaturePcg
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
     * Set sousnature
     *
     * @param \AppBundle\Entity\Sousnature $sousnature
     *
     * @return TdNdfSousnaturePcg
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
     * Set pcgTva
     *
     * @param \AppBundle\Entity\Pcg $pcgTva
     *
     * @return TdNdfSousnaturePcg
     */
    public function setPcgTva(\AppBundle\Entity\Pcg $pcgTva = null)
    {
        $this->pcgTva = $pcgTva;

        return $this;
    }

    /**
     * Get pcgTva
     *
     * @return \AppBundle\Entity\Pcg
     */
    public function getPcgTva()
    {
        return $this->pcgTva;
    }

    /**
     * Set pcgResultat
     *
     * @param \AppBundle\Entity\Pcg $pcgResultat
     *
     * @return TdNdfSousnaturePcg
     */
    public function setPcgResultat(\AppBundle\Entity\Pcg $pcgResultat = null)
    {
        $this->pcgResultat = $pcgResultat;

        return $this;
    }

    /**
     * Get pcgResultat
     *
     * @return \AppBundle\Entity\Pcg
     */
    public function getPcgResultat()
    {
        return $this->pcgResultat;
    }
}
