<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CorrespSoussouscategorie
 *
 * @ORM\Table(name="corresp_soussouscategorie", indexes={@ORM\Index(name="fk_sscateg_newid_idx", columns={"id_ancien"}), @ORM\Index(name="fk_sscateg_newid_idx1", columns={"id_nouvelle"}), @ORM\Index(name="fk_scateg_oldid_idx", columns={"id_souscateg"})})
 * @ORM\Entity
 */
class CorrespSoussouscategorie
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
     * @var \AppBundle\Entity\Soussouscategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Soussouscategorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_ancien", referencedColumnName="id")
     * })
     */
    private $idAncien;

    /**
     * @var \AppBundle\Entity\NewSscategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\NewSscategorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_nouvelle", referencedColumnName="id")
     * })
     */
    private $idNouvelle;

    /**
     * @var \AppBundle\Entity\Souscategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Souscategorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_souscateg", referencedColumnName="id")
     * })
     */
    private $idSouscateg;



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
     * @param \AppBundle\Entity\Soussouscategorie $idAncien
     *
     * @return CorrespSoussouscategorie
     */
    public function setIdAncien(\AppBundle\Entity\Soussouscategorie $idAncien = null)
    {
        $this->idAncien = $idAncien;

        return $this;
    }

    /**
     * Get idAncien
     *
     * @return \AppBundle\Entity\Soussouscategorie
     */
    public function getIdAncien()
    {
        return $this->idAncien;
    }

    /**
     * Set idNouvelle
     *
     * @param \AppBundle\Entity\NewSscategorie $idNouvelle
     *
     * @return CorrespSoussouscategorie
     */
    public function setIdNouvelle(\AppBundle\Entity\NewSscategorie $idNouvelle = null)
    {
        $this->idNouvelle = $idNouvelle;

        return $this;
    }

    /**
     * Get idNouvelle
     *
     * @return \AppBundle\Entity\NewSscategorie
     */
    public function getIdNouvelle()
    {
        return $this->idNouvelle;
    }

    /**
     * Set idSouscateg
     *
     * @param \AppBundle\Entity\Souscategorie $idSouscateg
     *
     * @return CorrespSoussouscategorie
     */
    public function setIdSouscateg(\AppBundle\Entity\Souscategorie $idSouscateg = null)
    {
        $this->idSouscateg = $idSouscateg;

        return $this;
    }

    /**
     * Get idSouscateg
     *
     * @return \AppBundle\Entity\Souscategorie
     */
    public function getIdSouscateg()
    {
        return $this->idSouscateg;
    }
}
