<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ParamAffectationEcheance
 *
 * @ORM\Table(name="param_affectation_echeance", indexes={@ORM\Index(name="fk_param_affectation_echeance_param_email_echeance1_idx", columns={"param_email_echeance_id"}), @ORM\Index(name="fk_param_affectation_echeance_dossier1_idx", columns={"dossier_id"})})
 * @ORM\Entity
 */
class ParamAffectationEcheance
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dernier_envoi", type="date", nullable=true)
     */
    private $dernierEnvoi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="prochain_envoi", type="date", nullable=false)
     */
    private $prochainEnvoi;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_envoi", type="integer", nullable=false)
     */
    private $nbEnvoi = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="actif", type="integer", nullable=false)
     */
    private $actif = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\ParamEmailEcheance
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ParamEmailEcheance")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="param_email_echeance_id", referencedColumnName="id")
     * })
     */
    private $paramEmailEcheance;

    /**
     * @var \AppBundle\Entity\Dossier
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Dossier")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dossier_id", referencedColumnName="id")
     * })
     */
    private $dossier;



    /**
     * Set dernierEnvoi
     *
     * @param \DateTime $dernierEnvoi
     *
     * @return ParamAffectationEcheance
     */
    public function setDernierEnvoi($dernierEnvoi)
    {
        $this->dernierEnvoi = $dernierEnvoi;

        return $this;
    }

    /**
     * Get dernierEnvoi
     *
     * @return \DateTime
     */
    public function getDernierEnvoi()
    {
        return $this->dernierEnvoi;
    }

    /**
     * Set prochainEnvoi
     *
     * @param \DateTime $prochainEnvoi
     *
     * @return ParamAffectationEcheance
     */
    public function setProchainEnvoi($prochainEnvoi)
    {
        $this->prochainEnvoi = $prochainEnvoi;

        return $this;
    }

    /**
     * Get prochainEnvoi
     *
     * @return \DateTime
     */
    public function getProchainEnvoi()
    {
        return $this->prochainEnvoi;
    }

    /**
     * Set nbEnvoi
     *
     * @param integer $nbEnvoi
     *
     * @return ParamAffectationEcheance
     */
    public function setNbEnvoi($nbEnvoi)
    {
        $this->nbEnvoi = $nbEnvoi;

        return $this;
    }

    /**
     * Get nbEnvoi
     *
     * @return integer
     */
    public function getNbEnvoi()
    {
        return $this->nbEnvoi;
    }

    /**
     * Set actif
     *
     * @param integer $actif
     *
     * @return ParamAffectationEcheance
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get actif
     *
     * @return integer
     */
    public function getActif()
    {
        return $this->actif;
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
     * Set paramEmailEcheance
     *
     * @param \AppBundle\Entity\ParamEmailEcheance $paramEmailEcheance
     *
     * @return ParamAffectationEcheance
     */
    public function setParamEmailEcheance(\AppBundle\Entity\ParamEmailEcheance $paramEmailEcheance = null)
    {
        $this->paramEmailEcheance = $paramEmailEcheance;

        return $this;
    }

    /**
     * Get paramEmailEcheance
     *
     * @return \AppBundle\Entity\ParamEmailEcheance
     */
    public function getParamEmailEcheance()
    {
        return $this->paramEmailEcheance;
    }

    /**
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return ParamAffectationEcheance
     */
    public function setDossier(\AppBundle\Entity\Dossier $dossier = null)
    {
        $this->dossier = $dossier;

        return $this;
    }

    /**
     * Get dossier
     *
     * @return \AppBundle\Entity\Dossier
     */
    public function getDossier()
    {
        return $this->dossier;
    }
}
