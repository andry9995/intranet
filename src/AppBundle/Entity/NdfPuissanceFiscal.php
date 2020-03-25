<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NdfPuissanceFiscal
 *
 * @ORM\Table(name="ndf_puissance_fiscal", indexes={@ORM\Index(name="fk_ndf_puissance_fiscal_type_vehicule1_idx", columns={"ndf_type_vehicule_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NdfPuissanceFiscalRepository")
 */
class NdfPuissanceFiscal
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
     * @ORM\Column(name="libelle", type="string", length=45, nullable=true)
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
     * @return NdfPuissanceFiscal
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
     * @return NdfPuissanceFiscal
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
     * @return NdfPuissanceFiscal
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
     * @return NdfPuissanceFiscal
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
