<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Criteres
 *
 * @ORM\Table(name="criteres", indexes={@ORM\Index(name="fk_criteres_dossier1_idx", columns={"dossier_id"}), @ORM\Index(name="fk_criteres_banque1_idx", columns={"banque_id"}), @ORM\Index(name="fk_criteres_type_tiers_id_idx", columns={"type_tiers_id"}), @ORM\Index(name="fk_criteres_type_compta_id_idx", columns={"type_compta_id"})})
 * @ORM\Entity
 */
class Criteres
{
    /**
     * @var string
     *
     * @ORM\Column(name="critere1", type="string", length=100, nullable=true)
     */
    private $critere1;

    /**
     * @var string
     *
     * @ORM\Column(name="critere2", type="string", length=100, nullable=true)
     */
    private $critere2;

    /**
     * @var string
     *
     * @ORM\Column(name="critere3", type="string", length=100, nullable=true)
     */
    private $critere3;

    /**
     * @var string
     *
     * @ORM\Column(name="sens", type="string", length=1, nullable=true)
     */
    private $sens;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\TypeTiers
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TypeTiers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_tiers_id", referencedColumnName="id")
     * })
     */
    private $typeTiers;

    /**
     * @var \AppBundle\Entity\TypeCompta
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TypeCompta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_compta_id", referencedColumnName="id")
     * })
     */
    private $typeCompta;

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
     * @var \AppBundle\Entity\Banque
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Banque")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="banque_id", referencedColumnName="id")
     * })
     */
    private $banque;



    /**
     * Set critere1
     *
     * @param string $critere1
     *
     * @return Criteres
     */
    public function setCritere1($critere1)
    {
        $this->critere1 = $critere1;

        return $this;
    }

    /**
     * Get critere1
     *
     * @return string
     */
    public function getCritere1()
    {
        return $this->critere1;
    }

    /**
     * Set critere2
     *
     * @param string $critere2
     *
     * @return Criteres
     */
    public function setCritere2($critere2)
    {
        $this->critere2 = $critere2;

        return $this;
    }

    /**
     * Get critere2
     *
     * @return string
     */
    public function getCritere2()
    {
        return $this->critere2;
    }

    /**
     * Set critere3
     *
     * @param string $critere3
     *
     * @return Criteres
     */
    public function setCritere3($critere3)
    {
        $this->critere3 = $critere3;

        return $this;
    }

    /**
     * Get critere3
     *
     * @return string
     */
    public function getCritere3()
    {
        return $this->critere3;
    }

    /**
     * Set sens
     *
     * @param string $sens
     *
     * @return Criteres
     */
    public function setSens($sens)
    {
        $this->sens = $sens;

        return $this;
    }

    /**
     * Get sens
     *
     * @return string
     */
    public function getSens()
    {
        return $this->sens;
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
     * Set typeTiers
     *
     * @param \AppBundle\Entity\TypeTiers $typeTiers
     *
     * @return Criteres
     */
    public function setTypeTiers(\AppBundle\Entity\TypeTiers $typeTiers = null)
    {
        $this->typeTiers = $typeTiers;

        return $this;
    }

    /**
     * Get typeTiers
     *
     * @return \AppBundle\Entity\TypeTiers
     */
    public function getTypeTiers()
    {
        return $this->typeTiers;
    }

    /**
     * Set typeCompta
     *
     * @param \AppBundle\Entity\TypeCompta $typeCompta
     *
     * @return Criteres
     */
    public function setTypeCompta(\AppBundle\Entity\TypeCompta $typeCompta = null)
    {
        $this->typeCompta = $typeCompta;

        return $this;
    }

    /**
     * Get typeCompta
     *
     * @return \AppBundle\Entity\TypeCompta
     */
    public function getTypeCompta()
    {
        return $this->typeCompta;
    }

    /**
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return Criteres
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
     * Set banque
     *
     * @param \AppBundle\Entity\Banque $banque
     *
     * @return Criteres
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
}
