<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EtapeTraitement
 *
 * @ORM\Table(name="etape_traitement", uniqueConstraints={@ORM\UniqueConstraint(name="code_UNIQUE", columns={"code"})}, indexes={@ORM\Index(name="fk_etape_traitement_application_version1_idx", columns={"application_version_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EtapeTraitementRepository")
 */
class EtapeTraitement
{
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=15, nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=50, nullable=false)
     */
    private $libelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="type_etape", type="integer", nullable=false)
     */
    private $typeEtape = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="process", type="integer", nullable=false)
     */
    private $process = '0';

    /**
     * @var array
     *
     * @ORM\Column(name="postes", type="simple_array", nullable=true)
     */
    private $postes;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\ApplicationVersion
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ApplicationVersion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="application_version_id", referencedColumnName="id")
     * })
     */
    private $applicationVersion;


    public function __construct()
    {
        $this->postes = [];
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return EtapeTraitement
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return EtapeTraitement
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set typeEtape
     *
     * @param integer $typeEtape
     *
     * @return EtapeTraitement
     */
    public function setTypeEtape($typeEtape)
    {
        $this->typeEtape = $typeEtape;

        return $this;
    }

    /**
     * Get typeEtape
     *
     * @return integer
     */
    public function getTypeEtape()
    {
        return $this->typeEtape;
    }

    /**
     * Set process
     *
     * @param integer $process
     *
     * @return EtapeTraitement
     */
    public function setProcess($process)
    {
        $this->process = $process;

        return $this;
    }

    /**
     * Get process
     *
     * @return integer
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param array $postes
     * @return $this
     */
    public function setPostes($postes)
    {
        $this->postes = $postes;
        return $this;
    }

    /**
     * @return array
     */
    public function getPostes()
    {
        if ($this->postes && is_array($this->postes)) {
            return $this->postes;
        }
        return [];
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
     * Set applicationVersion
     *
     * @param \AppBundle\Entity\ApplicationVersion $applicationVersion
     *
     * @return EtapeTraitement
     */
    public function setApplicationVersion(\AppBundle\Entity\ApplicationVersion $applicationVersion = null)
    {
        $this->applicationVersion = $applicationVersion;

        return $this;
    }

    /**
     * Get applicationVersion
     *
     * @return \AppBundle\Entity\ApplicationVersion
     */
    public function getApplicationVersion()
    {
        return $this->applicationVersion;
    }
}
