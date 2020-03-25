<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Gamme
 *
 * @ORM\Table(name="gamme")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GammeRepository")
 */
class Gamme
{
    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=100, nullable=false)
     */
    private $nom;

    /**
     * @var array
     *
     * @ORM\Column(name="procedures", type="simple_array", nullable=true)
     */
    private $procedures;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Gamme
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
     * Set procedures
     *
     * @param array $procedures
     *
     * @return Gamme
     */
    public function setProcedures($procedures)
    {
        $this->procedures = $procedures;

        return $this;
    }

    /**
     * Get procedures
     *
     * @return array
     */
    public function getProcedures()
    {
        return $this->procedures;
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
}
