<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TdCaissePcgBilan
 *
 * @ORM\Table(name="td_caisse_pcg_bilan")
 * @ORM\Entity
 */
class TdCaissePcgBilan
{
    /**
     * @var string
     *
     * @ORM\Column(name="compte", type="string", length=45, nullable=false)
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
     * Set compte
     *
     * @param string $compte
     *
     * @return TdCaissePcgBilan
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
}
