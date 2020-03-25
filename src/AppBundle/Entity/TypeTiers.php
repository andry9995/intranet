<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TypeTiers
 *
 * @ORM\Table(name="type_tiers")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TypeTiersRepository")
 */
class TypeTiers
{
    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=50, nullable=true)
     */
    private $libelle;

    /**
     * @var  integer
     *
     * @ORM\Column(name="saisie_banque", type="integer", nullable=true)
     */
    private $saisieBanque;


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
     * @return TypeTiers
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
     * @param $saisieBanque
     * @return $this
     */
    public function setSaisieBanque($saisieBanque)
    {
        $this->saisieBanque = $saisieBanque;

        return $this;
    }

    public function getSaisieBanque()
    {
        return $this->saisieBanque;
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
