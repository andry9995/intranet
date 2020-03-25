<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReglePaiement
 *
 * @ORM\Table(name="regle_paiement", uniqueConstraints={@ORM\UniqueConstraint(name="dossier_id_UNIQUE", columns={"dossier_id"})})
 * @ORM\Entity
 */
class ReglePaiement
{
    /**
     * @var integer
     *
     * @ORM\Column(name="type_date", type="integer", nullable=true)
     */
    private $typeDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbre_jour", type="integer", nullable=true)
     */
    private $nbreJour = '45';

    /**
     * @var integer
     *
     * @ORM\Column(name="date_le", type="integer", nullable=true)
     */
    private $dateLe;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * Set typeDate
     *
     * @param integer $typeDate
     *
     * @return ReglePaiement
     */
    public function setTypeDate($typeDate)
    {
        $this->typeDate = $typeDate;

        return $this;
    }

    /**
     * Get typeDate
     *
     * @return integer
     */
    public function getTypeDate()
    {
        return $this->typeDate;
    }

    /**
     * Set nbreJour
     *
     * @param integer $nbreJour
     *
     * @return ReglePaiement
     */
    public function setNbreJour($nbreJour)
    {
        $this->nbreJour = $nbreJour;

        return $this;
    }

    /**
     * Get nbreJour
     *
     * @return integer
     */
    public function getNbreJour()
    {
        return $this->nbreJour;
    }

    /**
     * Set dateLe
     *
     * @param integer $dateLe
     *
     * @return ReglePaiement
     */
    public function setDateLe($dateLe)
    {
        $this->dateLe = $dateLe;

        return $this;
    }

    /**
     * Get dateLe
     *
     * @return integer
     */
    public function getDateLe()
    {
        return $this->dateLe;
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
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return ReglePaiement
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
}
