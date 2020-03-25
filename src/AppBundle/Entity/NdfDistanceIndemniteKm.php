<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NdfDistanceIndemniteKm
 *
 * @ORM\Table(name="ndf_distance_indemnite_km", indexes={@ORM\Index(name="fk_ndf_dk_type_vehicule1_idx", columns={"ndf_type_vehicule_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NdfDistanceIndemniteKmRepository")
 */
class NdfDistanceIndemniteKm
{
    /**
     * @var integer
     *
     * @ORM\Column(name="min", type="integer", nullable=false)
     */
    private $min;

    /**
     * @var integer
     *
     * @ORM\Column(name="max", type="integer", nullable=false)
     */
    private $max;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=45, nullable=false)
     */
    private $libelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\NdfTypeVehicule
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\NdfTypeVehicule")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ndf_type_vehicule_id", referencedColumnName="id")
     * })
     */
    private $ndfTypeVehicule;



    /**
     * Set min
     *
     * @param integer $min
     *
     * @return NdfDistanceIndemniteKm
     */
    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * Get min
     *
     * @return integer
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Set max
     *
     * @param integer $max
     *
     * @return NdfDistanceIndemniteKm
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * Get max
     *
     * @return integer
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return NdfDistanceIndemniteKm
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ndfTypeVehicule
     *
     * @param \AppBundle\Entity\NdfTypeVehicule $ndfTypeVehicule
     *
     * @return NdfDistanceIndemniteKm
     */
    public function setNdfTypeVehicule(\AppBundle\Entity\NdfTypeVehicule $ndfTypeVehicule = null)
    {
        $this->ndfTypeVehicule = $ndfTypeVehicule;

        return $this;
    }

    /**
     * Get ndfTypeVehicule
     *
     * @return \AppBundle\Entity\NdfTypeVehicule
     */
    public function getNdfTypeVehicule()
    {
        return $this->ndfTypeVehicule;
    }
}
