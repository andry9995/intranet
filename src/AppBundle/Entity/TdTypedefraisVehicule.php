<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TdTypedefraisVehicule
 *
 * @ORM\Table(name="td_typedefrais_vehicule", indexes={@ORM\Index(name="fk_tdndftypedefrais_vehicule_typefrais_id_idx", columns={"type_frais_id"})})
 * @ORM\Entity
 */
class TdTypedefraisVehicule
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
     * @return TdTypedefraisVehicule
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
