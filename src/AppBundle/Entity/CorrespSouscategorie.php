<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CorrespSouscategorie
 *
 * @ORM\Table(name="corresp_souscategorie", indexes={@ORM\Index(name="fk_souscateg_souscatid_idx", columns={"id_ancien"}), @ORM\Index(name="fk_souscateg_newsouscatid_idx", columns={"id_nouvelle"}), @ORM\Index(name="fk_categ_newsousid_idx", columns={"id_categ"})})
 * @ORM\Entity
 */
class CorrespSouscategorie
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
     * @var \AppBundle\Entity\Souscategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Souscategorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ancien", referencedColumnName="id")
     * })
     */
    private $idAncien;

    /**
     * @var \AppBundle\Entity\NewSouscategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\NewSouscategorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_nouvelle", referencedColumnName="id")
     * })
     */
    private $idNouvelle;

    /**
     * @var \AppBundle\Entity\Categorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Categorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_categ", referencedColumnName="id")
     * })
     */
    private $idCateg;



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
     * Set idAncien
     *
     * @param \AppBundle\Entity\Souscategorie $idAncien
     *
     * @return CorrespSouscategorie
     */
    public function setIdAncien(\AppBundle\Entity\Souscategorie $idAncien = null)
    {
        $this->idAncien = $idAncien;

        return $this;
    }

    /**
     * Get idAncien
     *
     * @return \AppBundle\Entity\Souscategorie
     */
    public function getIdAncien()
    {
        return $this->idAncien;
    }

    /**
     * Set idNouvelle
     *
     * @param \AppBundle\Entity\NewSouscategorie $idNouvelle
     *
     * @return CorrespSouscategorie
     */
    public function setIdNouvelle(\AppBundle\Entity\NewSouscategorie $idNouvelle = null)
    {
        $this->idNouvelle = $idNouvelle;

        return $this;
    }

    /**
     * Get idNouvelle
     *
     * @return \AppBundle\Entity\NewSouscategorie
     */
    public function getIdNouvelle()
    {
        return $this->idNouvelle;
    }

    /**
     * Set idCateg
     *
     * @param \AppBundle\Entity\Categorie $idCateg
     *
     * @return CorrespSouscategorie
     */
    public function setIdCateg(\AppBundle\Entity\Categorie $idCateg = null)
    {
        $this->idCateg = $idCateg;

        return $this;
    }

    /**
     * Get idCateg
     *
     * @return \AppBundle\Entity\Categorie
     */
    public function getIdCateg()
    {
        return $this->idCateg;
    }
}
