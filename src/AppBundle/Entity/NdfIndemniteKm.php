<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NdfIndemniteKm
 *
 * @ORM\Table(name="ndf_indemnite_km", indexes={@ORM\Index(name="fk_ndf_ik_distance_km1_idx", columns={"ndf_distance_indemnite_km_id"}), @ORM\Index(name="fk_ndf_puissance_fiscal1_idx", columns={"ndf_puissance_fiscal_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NdfIndemniteKmRepository")
 */
class NdfIndemniteKm
{
    /**
     * @var integer
     *
     * @ORM\Column(name="exercice", type="integer", nullable=false)
     */
    private $exercice;

    /**
     * @var string
     *
     * @ORM\Column(name="formule", type="string", length=45, nullable=false)
     */
    private $formule;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\NdfDistanceIndemniteKm
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\NdfDistanceIndemniteKm")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ndf_distance_indemnite_km_id", referencedColumnName="id")
     * })
     */
    private $ndfDistanceIndemniteKm;

    /**
     * @var \AppBundle\Entity\NdfPuissanceFiscal
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\NdfPuissanceFiscal")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ndf_puissance_fiscal_id", referencedColumnName="id")
     * })
     */
    private $ndfPuissanceFiscal;



    /**
     * Set exercice
     *
     * @param integer $exercice
     *
     * @return NdfIndemniteKm
     */
    public function setExercice($exercice)
    {
        $this->exercice = $exercice;

        return $this;
    }

    /**
     * Get exercice
     *
     * @return integer
     */
    public function getExercice()
    {
        return $this->exercice;
    }

    /**
     * Set formule
     *
     * @param string $formule
     *
     * @return NdfIndemniteKm
     */
    public function setFormule($formule)
    {
        $this->formule = $formule;

        return $this;
    }

    /**
     * Get formule
     *
     * @return string
     */
    public function getFormule()
    {
        return $this->formule;
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
     * Set ndfDistanceIndemniteKm
     *
     * @param \AppBundle\Entity\NdfDistanceIndemniteKm $ndfDistanceIndemniteKm
     *
     * @return NdfIndemniteKm
     */
    public function setNdfDistanceIndemniteKm(\AppBundle\Entity\NdfDistanceIndemniteKm $ndfDistanceIndemniteKm = null)
    {
        $this->ndfDistanceIndemniteKm = $ndfDistanceIndemniteKm;

        return $this;
    }

    /**
     * Get ndfDistanceIndemniteKm
     *
     * @return \AppBundle\Entity\NdfDistanceIndemniteKm
     */
    public function getNdfDistanceIndemniteKm()
    {
        return $this->ndfDistanceIndemniteKm;
    }

    /**
     * Set ndfPuissanceFiscal
     *
     * @param \AppBundle\Entity\NdfPuissanceFiscal $ndfPuissanceFiscal
     *
     * @return NdfIndemniteKm
     */
    public function setNdfPuissanceFiscal(\AppBundle\Entity\NdfPuissanceFiscal $ndfPuissanceFiscal = null)
    {
        $this->ndfPuissanceFiscal = $ndfPuissanceFiscal;

        return $this;
    }

    /**
     * Get ndfPuissanceFiscal
     *
     * @return \AppBundle\Entity\NdfPuissanceFiscal
     */
    public function getNdfPuissanceFiscal()
    {
        return $this->ndfPuissanceFiscal;
    }
}
