<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrestationFiscaleTache
 *
 * @ORM\Table(name="prestation_fiscale_tache")
 * @ORM\Entity
 */
class PrestationFiscaleTache
{
    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=45, nullable=true)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="champs", type="string", length=200, nullable=true)
     */
    private $champs;

    /**
     * @var string
     *
     * @ORM\Column(name="taches", type="string", length=200, nullable=true)
     */
    private $taches;

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
     * @return PrestationFiscaleTache
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
     * Set champs
     *
     * @param string $champs
     *
     * @return PrestationFiscaleTache
     */
    public function setChamps($champs)
    {
        $this->champs = $champs;

        return $this;
    }

    /**
     * Get champs
     *
     * @return string
     */
    public function getChamps()
    {
        return $this->champs;
    }

    /**
     * Set taches
     *
     * @param string $taches
     *
     * @return PrestationFiscaleTache
     */
    public function setTaches($taches)
    {
        $this->taches = $taches;

        return $this;
    }

    /**
     * Get taches
     *
     * @return string
     */
    public function getTaches()
    {
        return $this->taches;
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
