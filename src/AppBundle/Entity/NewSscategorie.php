<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NewSscategorie
 *
 * @ORM\Table(name="new_sscategorie", indexes={@ORM\Index(name="fk_newsouscateg_idnewsouscateg_idx", columns={"id_souscateg"})})
 * @ORM\Entity
 */
class NewSscategorie
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
     * @ORM\Column(name="multi_exercice", type="integer", nullable=true)
     */
    private $multiExercice = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="dossier_permanent", type="integer", nullable=true)
     */
    private $dossierPermanent = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\NewSouscategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\NewSouscategorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_souscateg", referencedColumnName="id")
     * })
     */
    private $idSouscateg;



    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return NewSscategorie
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
     * Set multiExercice
     *
     * @param integer $multiExercice
     *
     * @return NewSscategorie
     */
    public function setMultiExercice($multiExercice)
    {
        $this->multiExercice = $multiExercice;

        return $this;
    }

    /**
     * Get multiExercice
     *
     * @return integer
     */
    public function getMultiExercice()
    {
        return $this->multiExercice;
    }

    /**
     * Set dossierPermanent
     *
     * @param integer $dossierPermanent
     *
     * @return NewSscategorie
     */
    public function setDossierPermanent($dossierPermanent)
    {
        $this->dossierPermanent = $dossierPermanent;

        return $this;
    }

    /**
     * Get dossierPermanent
     *
     * @return integer
     */
    public function getDossierPermanent()
    {
        return $this->dossierPermanent;
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
     * Set idSouscateg
     *
     * @param \AppBundle\Entity\NewSouscategorie $idSouscateg
     *
     * @return NewSscategorie
     */
    public function setIdSouscateg(\AppBundle\Entity\NewSouscategorie $idSouscateg = null)
    {
        $this->idSouscateg = $idSouscateg;

        return $this;
    }

    /**
     * Get idSouscateg
     *
     * @return \AppBundle\Entity\NewSouscategorie
     */
    public function getIdSouscateg()
    {
        return $this->idSouscateg;
    }
}
