<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CorrespCategorie
 *
 * @ORM\Table(name="corresp_categorie", indexes={@ORM\Index(name="fk_id_cate_ancien_idx", columns={"id_ancien"}), @ORM\Index(name="fk_id_cate_new_idx", columns={"id_nouvelle"})})
 * @ORM\Entity
 */
class CorrespCategorie
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
     * @var \AppBundle\Entity\NewCategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\NewCategorie")
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
     *   @ORM\JoinColumn(name="id_ancien", referencedColumnName="id")
     * })
     */
    private $idAncien;



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
     * Set idNouvelle
     *
     * @param \AppBundle\Entity\NewCategorie $idNouvelle
     *
     * @return CorrespCategorie
     */
    public function setIdNouvelle(\AppBundle\Entity\NewCategorie $idNouvelle = null)
    {
        $this->idNouvelle = $idNouvelle;

        return $this;
    }

    /**
     * Get idNouvelle
     *
     * @return \AppBundle\Entity\NewCategorie
     */
    public function getIdNouvelle()
    {
        return $this->idNouvelle;
    }

    /**
     * Set idAncien
     *
     * @param \AppBundle\Entity\Categorie $idAncien
     *
     * @return CorrespCategorie
     */
    public function setIdAncien(\AppBundle\Entity\Categorie $idAncien = null)
    {
        $this->idAncien = $idAncien;

        return $this;
    }

    /**
     * Get idAncien
     *
     * @return \AppBundle\Entity\Categorie
     */
    public function getIdAncien()
    {
        return $this->idAncien;
    }
}
