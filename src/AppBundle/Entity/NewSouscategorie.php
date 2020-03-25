<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NewSouscategorie
 *
 * @ORM\Table(name="new_souscategorie", indexes={@ORM\Index(name="fk_categ_idnew_idx", columns={"id_categ"})})
 * @ORM\Entity
 */
class NewSouscategorie
{
    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=150, nullable=true)
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
     * @var \AppBundle\Entity\NewCategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\NewCategorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_categ", referencedColumnName="id")
     * })
     */
    private $idCateg;



    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return NewSouscategorie
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
     * Set idCateg
     *
     * @param \AppBundle\Entity\NewCategorie $idCateg
     *
     * @return NewSouscategorie
     */
    public function setIdCateg(\AppBundle\Entity\NewCategorie $idCateg = null)
    {
        $this->idCateg = $idCateg;

        return $this;
    }

    /**
     * Get idCateg
     *
     * @return \AppBundle\Entity\NewCategorie
     */
    public function getIdCateg()
    {
        return $this->idCateg;
    }
}
