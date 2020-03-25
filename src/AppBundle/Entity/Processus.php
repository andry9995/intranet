<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Processus
 *
 * @ORM\Table(name="processus", indexes={@ORM\Index(name="fk_processus_processusid_idx", columns={"processus_id"}), @ORM\Index(name="fk_processus_processantid_idx", columns={"process_ant_id"}), @ORM\Index(name="fk_processus_processpostid_idx", columns={"process_post_id"}), @ORM\Index(name="fk_processus_uniteeuvrid_idx", columns={"unite_oeuvre_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProcessusRepository")
 */
class Processus
{
    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=250, nullable=true)
     */
    private $nom;

    /**
     * @var integer
     *
     * @ORM\Column(name="rang", type="integer", nullable=false)
     */
    private $rang;

    /**
     * @var integer
     *
     * @ORM\Column(name="actif", type="integer", nullable=true)
     */
    private $actif = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="temps_trait", type="integer", nullable=true)
     */
    private $tempsTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Processus
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Processus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="processus_id", referencedColumnName="id")
     * })
     */
    private $processus;

    /**
     * @var \AppBundle\Entity\UniteOeuvre
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\UniteOeuvre")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="unite_oeuvre_id", referencedColumnName="id")
     * })
     */
    private $uniteOeuvre;

    /**
     * @var \AppBundle\Entity\Processus
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Processus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="process_post_id", referencedColumnName="id")
     * })
     */
    private $processPost;

    /**
     * @var \AppBundle\Entity\Processus
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Processus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="process_ant_id", referencedColumnName="id")
     * })
     */
    private $processAnt;



    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Processus
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
     * Set rang
     *
     * @param integer $rang
     *
     * @return Processus
     */
    public function setRang($rang)
    {
        $this->rang = $rang;

        return $this;
    }

    /**
     * Get rang
     *
     * @return integer
     */
    public function getRang()
    {
        return $this->rang;
    }

    /**
     * Set actif
     *
     * @param integer $actif
     *
     * @return Processus
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
     * Set tempsTrait
     *
     * @param integer $tempsTrait
     *
     * @return Processus
     */
    public function setTempsTrait($tempsTrait)
    {
        $this->tempsTrait = $tempsTrait;

        return $this;
    }

    /**
     * Get tempsTrait
     *
     * @return integer
     */
    public function getTempsTrait()
    {
        return $this->tempsTrait;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Processus
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * Set processus
     *
     * @param \AppBundle\Entity\Processus $processus
     *
     * @return Processus
     */
    public function setProcessus(\AppBundle\Entity\Processus $processus = null)
    {
        $this->processus = $processus;

        return $this;
    }

    /**
     * Get processus
     *
     * @return \AppBundle\Entity\Processus
     */
    public function getProcessus()
    {
        return $this->processus;
    }

    /**
     * Set uniteOeuvre
     *
     * @param \AppBundle\Entity\UniteOeuvre $uniteOeuvre
     *
     * @return Processus
     */
    public function setUniteOeuvre(\AppBundle\Entity\UniteOeuvre $uniteOeuvre = null)
    {
        $this->uniteOeuvre = $uniteOeuvre;

        return $this;
    }

    /**
     * Get uniteOeuvre
     *
     * @return \AppBundle\Entity\UniteOeuvre
     */
    public function getUniteOeuvre()
    {
        return $this->uniteOeuvre;
    }

    /**
     * Set processPost
     *
     * @param \AppBundle\Entity\Processus $processPost
     *
     * @return Processus
     */
    public function setProcessPost(\AppBundle\Entity\Processus $processPost = null)
    {
        $this->processPost = $processPost;

        return $this;
    }

    /**
     * Get processPost
     *
     * @return \AppBundle\Entity\Processus
     */
    public function getProcessPost()
    {
        return $this->processPost;
    }

    /**
     * Set processAnt
     *
     * @param \AppBundle\Entity\Processus $processAnt
     *
     * @return Processus
     */
    public function setProcessAnt(\AppBundle\Entity\Processus $processAnt = null)
    {
        $this->processAnt = $processAnt;

        return $this;
    }

    /**
     * Get processAnt
     *
     * @return \AppBundle\Entity\Processus
     */
    public function getProcessAnt()
    {
        return $this->processAnt;
    }
}
