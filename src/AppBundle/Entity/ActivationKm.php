<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivationKm
 *
 * @ORM\Table(name="activation_km", indexes={@ORM\Index(name="fk_type_frais_idkm_idx", columns={"type_frais_id"})})
 * @ORM\Entity
 */
class ActivationKm
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\TypeFrais
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TypeFrais")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_frais_id", referencedColumnName="id")
     * })
     */
    private $typeFrais;



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
     * Set typeFrais
     *
     * @param \AppBundle\Entity\TypeFrais $typeFrais
     *
     * @return ActivationKm
     */
    public function setTypeFrais(\AppBundle\Entity\TypeFrais $typeFrais = null)
    {
        $this->typeFrais = $typeFrais;

        return $this;
    }

    /**
     * Get typeFrais
     *
     * @return \AppBundle\Entity\TypeFrais
     */
    public function getTypeFrais()
    {
        return $this->typeFrais;
    }
}
