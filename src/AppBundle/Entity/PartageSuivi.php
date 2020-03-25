<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PartageSuivi
 *
 * @ORM\Table(name="partage_suivi", uniqueConstraints={@ORM\UniqueConstraint(name="unique_categorie_lot", columns={"lot_id", "categorie_id", "etape_traitement_id"})}, indexes={@ORM\Index(name="fk_categorie_partage_suivi_idx", columns={"categorie_id"}), @ORM\Index(name="fk_lot_partage_suivi_idx", columns={"lot_id"}), @ORM\Index(name="fk_etape_traitement_partage_suivi_idx", columns={"etape_traitement_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PartageSuiviRepository")
 */
class PartageSuivi
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_partage", type="date", nullable=true)
     */
    private $datePartage;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Lot
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Lot")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     * })
     */
    private $lot;

    /**
     * @var \AppBundle\Entity\EtapeTraitement
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EtapeTraitement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="etape_traitement_id", referencedColumnName="id")
     * })
     */
    private $etapeTraitement;

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
     * Set datePartage
     *
     * @param \DateTime $datePartage
     *
     * @return PartageSuivi
     */
    public function setDatePartage($datePartage)
    {
        $this->datePartage = $datePartage;

        return $this;
    }

    /**
     * Get datePartage
     *
     * @return \DateTime
     */
    public function getDatePartage()
    {
        return $this->datePartage;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return PartageSuivi
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
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
     * Set lot
     *
     * @param \AppBundle\Entity\Lot $lot
     *
     * @return PartageSuivi
     */
    public function setLot(\AppBundle\Entity\Lot $lot = null)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * Get lot
     *
     * @return \AppBundle\Entity\Lot
     */
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * Set etapeTraitement
     *
     * @param \AppBundle\Entity\EtapeTraitement $etapeTraitement
     *
     * @return PartageSuivi
     */
    public function setEtapeTraitement(\AppBundle\Entity\EtapeTraitement $etapeTraitement = null)
    {
        $this->etapeTraitement = $etapeTraitement;

        return $this;
    }

    /**
     * Get etapeTraitement
     *
     * @return \AppBundle\Entity\EtapeTraitement
     */
    public function getEtapeTraitement()
    {
        return $this->etapeTraitement;
    }

    /**
     * Set categorie
     *
     * @param \AppBundle\Entity\Categorie $categorie
     *
     * @return PartageSuivi
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
