<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bnumcompte
 *
 * @ORM\Table(name="bnumcompte")
 * @ORM\Entity
 */
class Bnumcompte
{
    /**
     * @var string
     *
     * @ORM\Column(name="dossier", type="string", length=100, nullable=true)
     */
    private $dossier;

    /**
     * @var string
     *
     * @ORM\Column(name="banque", type="string", length=150, nullable=true)
     */
    private $banque;

    /**
     * @var string
     *
     * @ORM\Column(name="numcompte", type="string", length=45, nullable=true)
     */
    private $numcompte;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set dossier
     *
     * @param string $dossier
     *
     * @return Bnumcompte
     */
    public function setDossier($dossier)
    {
        $this->dossier = $dossier;

        return $this;
    }

    /**
     * Get dossier
     *
     * @return string
     */
    public function getDossier()
    {
        return $this->dossier;
    }

    /**
     * Set banque
     *
     * @param string $banque
     *
     * @return Bnumcompte
     */
    public function setBanque($banque)
    {
        $this->banque = $banque;

        return $this;
    }

    /**
     * Get banque
     *
     * @return string
     */
    public function getBanque()
    {
        return $this->banque;
    }

    /**
     * Set numcompte
     *
     * @param string $numcompte
     *
     * @return Bnumcompte
     */
    public function setNumcompte($numcompte)
    {
        $this->numcompte = $numcompte;

        return $this;
    }

    /**
     * Get numcompte
     *
     * @return string
     */
    public function getNumcompte()
    {
        return $this->numcompte;
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
