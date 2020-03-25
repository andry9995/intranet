<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SoussouscategorieCompte
 *
 * @ORM\Table(name="soussouscategorie_compte", indexes={@ORM\Index(name="fk_soussouscompte_pcgid_idx", columns={"pcg_id"}), @ORM\Index(name="fk_soussouscompte_soussouscateg_id_idx", columns={"soussouscategorie_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SoussouscategorieCompteRepository")
 */
class SoussouscategorieCompte
{
    /**
     * @var integer
     *
     * @ORM\Column(name="type_compte", type="integer", nullable=true)
     */
    private $typeCompte;

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
     *   @ORM\JoinColumn(name="soussouscategorie_id", referencedColumnName="id")
     * })
     */
    private $soussouscategorie;

    /**
     * @var \AppBundle\Entity\Pcg
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcg")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pcg_id", referencedColumnName="id")
     * })
     */
    private $pcg;



    /**
     * Set typeCompte
     *
     * @param integer $typeCompte
     *
     * @return SoussouscategorieCompte
     */
    public function setTypeCompte($typeCompte)
    {
        $this->typeCompte = $typeCompte;

        return $this;
    }

    /**
     * Get typeCompte
     *
     * @return integer
     */
    public function getTypeCompte()
    {
        return $this->typeCompte;
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
     * Set soussouscategorie
     *
     * @param \AppBundle\Entity\Soussouscategorie $soussouscategorie
     *
     * @return SoussouscategorieCompte
     */
    public function setSoussouscategorie(\AppBundle\Entity\Soussouscategorie $soussouscategorie = null)
    {
        $this->soussouscategorie = $soussouscategorie;

        return $this;
    }

    /**
     * Get soussouscategorie
     *
     * @return \AppBundle\Entity\Soussouscategorie
     */
    public function getSoussouscategorie()
    {
        return $this->soussouscategorie;
    }

    /**
     * Set pcg
     *
     * @param \AppBundle\Entity\Pcg $pcg
     *
     * @return SoussouscategorieCompte
     */
    public function setPcg(\AppBundle\Entity\Pcg $pcg = null)
    {
        $this->pcg = $pcg;

        return $this;
    }

    /**
     * Get pcg
     *
     * @return \AppBundle\Entity\Pcg
     */
    public function getPcg()
    {
        return $this->pcg;
    }
}
