<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TbimageZero
 *
 * @ORM\Table(name="tbimage_zero", indexes={@ORM\Index(name="fk_tbimage_zero_dossier1_idx", columns={"dossier_id"}), @ORM\Index(name="fk_tbimage_zero_categorie1_idx", columns={"categorie_id"}), @ORM\Index(name="fk_tb_image_zero_banque1_idx", columns={"banque_id"})})
 * @ORM\Entity
 */
class TbimageZero
{
    /**
     * @var integer
     *
     * @ORM\Column(name="exercice", type="integer", nullable=false)
     */
    private $exercice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="mois", type="date", nullable=false)
     */
    private $mois;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Banque
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Banque")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="banque_id", referencedColumnName="id")
     * })
     */
    private $banque;

    /**
     * @var \AppBundle\Entity\Dossier
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Dossier")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dossier_id", referencedColumnName="id")
     * })
     */
    private $dossier;

    /**
     * @var \AppBundle\Entity\Categorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Categorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="categorie_id", referencedColumnName="id")
     * })
     */
    private $categorie;



    /**
     * Set exercice
     *
     * @param integer $exercice
     *
     * @return TbimageZero
     */
    public function setExercice($exercice)
    {
        $this->exercice = $exercice;

        return $this;
    }

    /**
     * Get exercice
     *
     * @return integer
     */
    public function getExercice()
    {
        return $this->exercice;
    }

    /**
     * Set mois
     *
     * @param \DateTime $mois
     *
     * @return TbimageZero
     */
    public function setMois($mois)
    {
        $this->mois = $mois;

        return $this;
    }

    /**
     * Get mois
     *
     * @return \DateTime
     */
    public function getMois()
    {
        return $this->mois;
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
     * Set banque
     *
     * @param \AppBundle\Entity\Banque $banque
     *
     * @return TbimageZero
     */
    public function setBanque(\AppBundle\Entity\Banque $banque = null)
    {
        $this->banque = $banque;

        return $this;
    }

    /**
     * Get banque
     *
     * @return \AppBundle\Entity\Banque
     */
    public function getBanque()
    {
        return $this->banque;
    }

    /**
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return TbimageZero
     */
    public function setDossier(\AppBundle\Entity\Dossier $dossier = null)
    {
        $this->dossier = $dossier;

        return $this;
    }

    /**
     * Get dossier
     *
     * @return \AppBundle\Entity\Dossier
     */
    public function getDossier()
    {
        return $this->dossier;
    }

    /**
     * Set categorie
     *
     * @param \AppBundle\Entity\Categorie $categorie
     *
     * @return TbimageZero
     */
    public function setCategorie(\AppBundle\Entity\Categorie $categorie = null)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return \AppBundle\Entity\Categorie
     */
    public function getCategorie()
    {
        return $this->categorie;
    }
}
