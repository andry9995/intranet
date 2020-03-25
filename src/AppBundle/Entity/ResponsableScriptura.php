<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ResponsableScriptura
 *
 * @ORM\Table(name="responsable_scriptura", indexes={@ORM\Index(name="fk_responsable_operateur1_idx", columns={"operateur_id"}), @ORM\Index(name="fk_responsable_responsable1_idx", columns={"superieur"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ResponsableScripturaRepository")
 */
class ResponsableScriptura
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
     *   @ORM\JoinColumn(name="operateur_id", referencedColumnName="id")
     * })
     */
    private $operateur;

    /**
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="superieur", referencedColumnName="id")
     * })
     */
    private $superieur;



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
     * Set operateur
     *
     * @param Operateur $operateur
     *
     * @return ResponsableScriptura
     */
    public function setOperateur(Operateur $operateur = null)
    {
        $this->operateur = $operateur;

        return $this;
    }

    /**
     * Get operateur
     *
     * @return Operateur
     */
    public function getOperateur()
    {
        return $this->operateur;
    }

    /**
     * Set responsable
     *
     * @param Operateur $superieur
     *
     * @return ResponsableScriptura
     */
    public function setSuperieur(Operateur $superieur = null)
    {
        $this->superieur = $superieur;

        return $this;
    }

    /**
     * Get superieur
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getSuperieur()
    {
        return $this->superieur;
    }
}
