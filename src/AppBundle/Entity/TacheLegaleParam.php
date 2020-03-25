<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TacheLegaleParam
 *
 * @ORM\Table(name="tache_legale_param", indexes={@ORM\Index(name="fk_tache_legale_param_action1_idx", columns={"tache_legale_action_id"}), @ORM\Index(name="fk_tache_legale_dossier1_idx", columns={"dossier_id"}), @ORM\Index(name="fk_tache_legale_param_utilisateur1_idx", columns={"utilisateur_id"}), @ORM\Index(name="fk_tache_legale_param_operateur_idx", columns={"operateur_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TacheLegaleParamRepository")
 */
class TacheLegaleParam
{
    /**
     * @var integer
     *
     * @ORM\Column(name="plus_tard", type="integer", nullable=true)
     */
    private $plusTard;

    /**
     * @var integer
     *
     * @ORM\Column(name="realiser_avant", type="integer", nullable=true)
     */
    private $realiserAvant;

    /**
     * @var integer
     *
     * @ORM\Column(name="entite", type="integer", nullable=true)
     */
    private $entite;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="demarrage", type="date", nullable=true)
     */
    private $demarrage;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id")
     * })
     */
    private $utilisateur;

    /**
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="operateur_id", referencedColumnName="id")
     * })
     */
    private $operateur;

    /**
     * @var \AppBundle\Entity\TacheLegaleAction
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TacheLegaleAction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_legale_action_id", referencedColumnName="id")
     * })
     */
    private $tacheLegaleAction;

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
     * Set plusTard
     *
     * @param integer $plusTard
     *
     * @return TacheLegaleParam
     */
    public function setPlusTard($plusTard)
    {
        $this->plusTard = $plusTard;

        return $this;
    }

    /**
     * Get plusTard
     *
     * @return integer
     */
    public function getPlusTard()
    {
        return $this->plusTard;
    }

    /**
     * Set realiserAvant
     *
     * @param integer $realiserAvant
     *
     * @return TacheLegaleParam
     */
    public function setRealiserAvant($realiserAvant)
    {
        $this->realiserAvant = $realiserAvant;

        return $this;
    }

    /**
     * Get realiserAvant
     *
     * @return integer
     */
    public function getRealiserAvant()
    {
        return $this->realiserAvant;
    }

    /**
     * Set entite
     *
     * @param integer $entite
     *
     * @return TacheLegaleParam
     */
    public function setEntite($entite)
    {
        $this->entite = $entite;

        return $this;
    }

    /**
     * Get entite
     *
     * @return integer
     */
    public function getEntite()
    {
        return $this->entite;
    }

    /**
     * Set demarrage
     *
     * @param \DateTime $demarrage
     *
     * @return TacheLegaleParam
     */
    public function setDemarrage($demarrage)
    {
        $this->demarrage = $demarrage;

        return $this;
    }

    /**
     * Get demarrage
     *
     * @return \DateTime
     */
    public function getDemarrage()
    {
        return $this->demarrage;
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
     * Set utilisateur
     *
     * @param \AppBundle\Entity\Utilisateur $utilisateur
     *
     * @return TacheLegaleParam
     */
    public function setUtilisateur(\AppBundle\Entity\Utilisateur $utilisateur = null)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur
     *
     * @return \AppBundle\Entity\Utilisateur
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set operateur
     *
     * @param \AppBundle\Entity\Operateur $operateur
     *
     * @return TacheLegaleParam
     */
    public function setOperateur(\AppBundle\Entity\Operateur $operateur = null)
    {
        $this->operateur = $operateur;

        return $this;
    }

    /**
     * Get operateur
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getOperateur()
    {
        return $this->operateur;
    }

    /**
     * Set tacheLegaleAction
     *
     * @param \AppBundle\Entity\TacheLegaleAction $tacheLegaleAction
     *
     * @return TacheLegaleParam
     */
    public function setTacheLegaleAction(\AppBundle\Entity\TacheLegaleAction $tacheLegaleAction = null)
    {
        $this->tacheLegaleAction = $tacheLegaleAction;

        return $this;
    }

    /**
     * Get tacheLegaleAction
     *
     * @return \AppBundle\Entity\TacheLegaleAction
     */
    public function getTacheLegaleAction()
    {
        return $this->tacheLegaleAction;
    }

    /**
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return TacheLegaleParam
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
