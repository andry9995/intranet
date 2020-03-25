<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NewCategorie
 *
 * @ORM\Table(name="new_categorie")
 * @ORM\Entity
 */
class NewCategorie
{
    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=50, nullable=true)
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
     * Set libelle
     *
     * @param string $libelle
     *
     * @return NewCategorie
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
}
