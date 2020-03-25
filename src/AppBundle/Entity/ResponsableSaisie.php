<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ResponsableSaisie
 *
 * @ORM\Table(name="responsable_saisie", indexes={@ORM\Index(name="fk_id_dest_op_idx", columns={"id_operateur"})})
 * @ORM\Entity
 */
class ResponsableSaisie
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
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_operateur", referencedColumnName="id")
     * })
     */
    private $idOperateur;



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
     * Set idOperateur
     *
     * @param \AppBundle\Entity\Operateur $idOperateur
     *
     * @return ResponsableSaisie
     */
    public function setIdOperateur(\AppBundle\Entity\Operateur $idOperateur = null)
    {
        $this->idOperateur = $idOperateur;

        return $this;
    }

    /**
     * Get idOperateur
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getIdOperateur()
    {
        return $this->idOperateur;
    }
}
