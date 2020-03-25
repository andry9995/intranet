<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TdCaissePcgResultat
 *
 * @ORM\Table(name="td_caisse_pcg_resultat", uniqueConstraints={@ORM\UniqueConstraint(name="unique", columns={"caisse_nature_id", "compte"})}, indexes={@ORM\Index(name="fk_td_caisse_pcg_resultat_caisse_nature1_idx", columns={"caisse_nature_id"})})
 * @ORM\Entity
 */
class TdCaissePcgResultat
{
    /**
     * @var string
     *
     * @ORM\Column(name="compte", type="string", length=45, nullable=true)
     */
    private $compte;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\CaisseNature
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CaisseNature")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="caisse_nature_id", referencedColumnName="id")
     * })
     */
    private $caisseNature;



    /**
     * Set compte
     *
     * @param string $compte
     *
     * @return TdCaissePcgResultat
     */
    public function setCompte($compte)
    {
        $this->compte = $compte;

        return $this;
    }

    /**
     * Get compte
     *
     * @return string
     */
    public function getCompte()
    {
        return $this->compte;
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
     * Set caisseNature
     *
     * @param \AppBundle\Entity\CaisseNature $caisseNature
     *
     * @return TdCaissePcgResultat
     */
    public function setCaisseNature(\AppBundle\Entity\CaisseNature $caisseNature = null)
    {
        $this->caisseNature = $caisseNature;

        return $this;
    }

    /**
     * Get caisseNature
     *
     * @return \AppBundle\Entity\CaisseNature
     */
    public function getCaisseNature()
    {
        return $this->caisseNature;
    }
}
