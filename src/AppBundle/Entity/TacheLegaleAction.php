<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TacheLegaleAction
 *
 * @ORM\Table(name="tache_legale_action", indexes={@ORM\Index(name="fk_tache_legale_tache_legale_action1_idx", columns={"tache_legale_id"}), @ORM\Index(name="fk_tache_legale_action_tache_liste_action_idx", columns={"tache_liste_action_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TacheLegaleActionRepository")
 */
class TacheLegaleAction
{
    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="date_action", type="text", length=65535, nullable=true)
     */
    private $dateAction;

    /**
     * @var string
     *
     * @ORM\Column(name="cerfa", type="string", length=50, nullable=true)
     */
    private $cerfa;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire1", type="text", length=65535, nullable=true)
     */
    private $commentaire1;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire2", type="text", length=65535, nullable=true)
     */
    private $commentaire2;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire3", type="text", length=65535, nullable=true)
     */
    private $commentaire3;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire4", type="text", length=65535, nullable=true)
     */
    private $commentaire4;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\TacheLegale
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TacheLegale")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_legale_id", referencedColumnName="id")
     * })
     */
    private $tacheLegale;

    /**
     * @var \AppBundle\Entity\TacheListeAction
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TacheListeAction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_liste_action_id", referencedColumnName="id")
     * })
     */
    private $tacheListeAction;



    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return TacheLegaleAction
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set dateAction
     *
     * @param string $dateAction
     *
     * @return TacheLegaleAction
     */
    public function setDateAction($dateAction)
    {
        $this->dateAction = $dateAction;

        return $this;
    }

    /**
     * Get dateAction
     *
     * @return string
     */
    public function getDateAction()
    {
        return $this->dateAction;
    }

    /**
     * Set cerfa
     *
     * @param string $cerfa
     *
     * @return TacheLegaleAction
     */
    public function setCerfa($cerfa)
    {
        $this->cerfa = $cerfa;

        return $this;
    }

    /**
     * Get cerfa
     *
     * @return string
     */
    public function getCerfa()
    {
        return $this->cerfa;
    }

    /**
     * Set commentaire1
     *
     * @param string $commentaire1
     *
     * @return TacheLegaleAction
     */
    public function setCommentaire1($commentaire1)
    {
        $this->commentaire1 = $commentaire1;

        return $this;
    }

    /**
     * Get commentaire1
     *
     * @return string
     */
    public function getCommentaire1()
    {
        return $this->commentaire1;
    }

    /**
     * Set commentaire2
     *
     * @param string $commentaire2
     *
     * @return TacheLegaleAction
     */
    public function setCommentaire2($commentaire2)
    {
        $this->commentaire2 = $commentaire2;

        return $this;
    }

    /**
     * Get commentaire2
     *
     * @return string
     */
    public function getCommentaire2()
    {
        return $this->commentaire2;
    }

    /**
     * Set commentaire3
     *
     * @param string $commentaire3
     *
     * @return TacheLegaleAction
     */
    public function setCommentaire3($commentaire3)
    {
        $this->commentaire3 = $commentaire3;

        return $this;
    }

    /**
     * Get commentaire3
     *
     * @return string
     */
    public function getCommentaire3()
    {
        return $this->commentaire3;
    }

    /**
     * Set commentaire4
     *
     * @param string $commentaire4
     *
     * @return TacheLegaleAction
     */
    public function setCommentaire4($commentaire4)
    {
        $this->commentaire4 = $commentaire4;

        return $this;
    }

    /**
     * Get commentaire4
     *
     * @return string
     */
    public function getCommentaire4()
    {
        return $this->commentaire4;
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
     * Set tacheLegale
     *
     * @param \AppBundle\Entity\TacheLegale $tacheLegale
     *
     * @return TacheLegaleAction
     */
    public function setTacheLegale(\AppBundle\Entity\TacheLegale $tacheLegale = null)
    {
        $this->tacheLegale = $tacheLegale;

        return $this;
    }

    /**
     * Get tacheLegale
     *
     * @return \AppBundle\Entity\TacheLegale
     */
    public function getTacheLegale()
    {
        return $this->tacheLegale;
    }

    /**
     * Set tacheListeAction
     *
     * @param \AppBundle\Entity\TacheListeAction $tacheListeAction
     *
     * @return TacheLegaleAction
     */
    public function setTacheListeAction(\AppBundle\Entity\TacheListeAction $tacheListeAction = null)
    {
        $this->tacheListeAction = $tacheListeAction;

        return $this;
    }

    /**
     * Get tacheListeAction
     *
     * @return \AppBundle\Entity\TacheListeAction
     */
    public function getTacheListeAction()
    {
        return $this->tacheListeAction;
    }
}
