<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OneEncaissementDetail
 *
 * @ORM\Table(name="one_encaissement_detail", indexes={@ORM\Index(name="fk_one_encaissement_detail_encaissement1_idx", columns={"one_encaissement_id"}), @ORM\Index(name="fk_one_encaissement_detail_one_compte1_idx", columns={"one_compte_id"}), @ORM\Index(name="fk_one_encaissement_detail_tva_taux1_idx", columns={"tva_taux_id"}), @ORM\Index(name="fk_one_encaissement_detail_one_projet1_idx", columns={"one_projet_id"})})
 * @ORM\Entity
 */
class OneEncaissementDetail
{
    /**
     * @var float
     *
     * @ORM\Column(name="montant", type="float", precision=10, scale=0, nullable=true)
     */
    private $montant;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", length=65535, nullable=true)
     */
    private $note;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\TvaTaux
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TvaTaux")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tva_taux_id", referencedColumnName="id")
     * })
     */
    private $tvaTaux;

    /**
     * @var \AppBundle\Entity\OneProjet
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OneProjet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="one_projet_id", referencedColumnName="id")
     * })
     */
    private $oneProjet;

    /**
     * @var \AppBundle\Entity\OneCompte
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OneCompte")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="one_compte_id", referencedColumnName="id")
     * })
     */
    private $oneCompte;

    /**
     * @var \AppBundle\Entity\OneEncaissement
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OneEncaissement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="one_encaissement_id", referencedColumnName="id")
     * })
     */
    private $oneEncaissement;



    /**
     * Set montant
     *
     * @param float $montant
     *
     * @return OneEncaissementDetail
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant
     *
     * @return float
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return OneEncaissementDetail
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
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
     * Set tvaTaux
     *
     * @param \AppBundle\Entity\TvaTaux $tvaTaux
     *
     * @return OneEncaissementDetail
     */
    public function setTvaTaux(\AppBundle\Entity\TvaTaux $tvaTaux = null)
    {
        $this->tvaTaux = $tvaTaux;

        return $this;
    }

    /**
     * Get tvaTaux
     *
     * @return \AppBundle\Entity\TvaTaux
     */
    public function getTvaTaux()
    {
        return $this->tvaTaux;
    }

    /**
     * Set oneProjet
     *
     * @param \AppBundle\Entity\OneProjet $oneProjet
     *
     * @return OneEncaissementDetail
     */
    public function setOneProjet(\AppBundle\Entity\OneProjet $oneProjet = null)
    {
        $this->oneProjet = $oneProjet;

        return $this;
    }

    /**
     * Get oneProjet
     *
     * @return \AppBundle\Entity\OneProjet
     */
    public function getOneProjet()
    {
        return $this->oneProjet;
    }

    /**
     * Set oneCompte
     *
     * @param \AppBundle\Entity\OneCompte $oneCompte
     *
     * @return OneEncaissementDetail
     */
    public function setOneCompte(\AppBundle\Entity\OneCompte $oneCompte = null)
    {
        $this->oneCompte = $oneCompte;

        return $this;
    }

    /**
     * Get oneCompte
     *
     * @return \AppBundle\Entity\OneCompte
     */
    public function getOneCompte()
    {
        return $this->oneCompte;
    }

    /**
     * Set oneEncaissement
     *
     * @param \AppBundle\Entity\OneEncaissement $oneEncaissement
     *
     * @return OneEncaissementDetail
     */
    public function setOneEncaissement(\AppBundle\Entity\OneEncaissement $oneEncaissement = null)
    {
        $this->oneEncaissement = $oneEncaissement;

        return $this;
    }

    /**
     * Get oneEncaissement
     *
     * @return \AppBundle\Entity\OneEncaissement
     */
    public function getOneEncaissement()
    {
        return $this->oneEncaissement;
    }
}
