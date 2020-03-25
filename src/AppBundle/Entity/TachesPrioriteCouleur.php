<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TachesPrioriteCouleur
 *
 * @ORM\Table(name="taches_priorite_couleur", uniqueConstraints={@ORM\UniqueConstraint(name="code_couleur_UNIQUE", columns={"code_couleur"})})
 * @ORM\Entity
 */
class TachesPrioriteCouleur
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
     * @ORM\Column(name="code_couleur", type="string", length=45, nullable=false)
     */
    private $codeCouleur;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set min
     *
     * @param integer $min
     *
     * @return TachesPrioriteCouleur
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
     * @return TachesPrioriteCouleur
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
     * Set codeCouleur
     *
     * @param string $codeCouleur
     *
     * @return TachesPrioriteCouleur
     */
    public function setCodeCouleur($codeCouleur)
    {
        $this->codeCouleur = $codeCouleur;

        return $this;
    }

    /**
     * Get codeCouleur
     *
     * @return string
     */
    public function getCodeCouleur()
    {
        return $this->codeCouleur;
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
