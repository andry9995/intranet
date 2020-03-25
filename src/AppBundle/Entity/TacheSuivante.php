<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TacheSuivante
 *
 * @ORM\Table(name="tache_suivante", uniqueConstraints={@ORM\UniqueConstraint(name="unique_tache_principale_suivant", columns={"tache_suivante", "tache_principale"})}, indexes={@ORM\Index(name="fk_tache_tache_suivant_principale_idx", columns={"tache_principale"}), @ORM\Index(name="IDX_AC5DB33FAC5DB33F", columns={"tache_suivante"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TacheSuivanteRepository")
 */
class TacheSuivante
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="integer", nullable=false)
     */
    private $ordre = '100';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Tache
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Tache")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_principale", referencedColumnName="id")
     * })
     */
    private $tachePrincipale;

    /**
     * @var \AppBundle\Entity\Tache
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Tache")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tache_suivante", referencedColumnName="id")
     * })
     */
    private $tacheSuivante;



    /**
     * Set ordre
     *
     * @param integer $ordre
     *
     * @return TacheSuivante
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer
     */
    public function getOrdre()
    {
        return $this->ordre;
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
     * Set tachePrincipale
     *
     * @param \AppBundle\Entity\Tache $tachePrincipale
     *
     * @return TacheSuivante
     */
    public function setTachePrincipale(\AppBundle\Entity\Tache $tachePrincipale = null)
    {
        $this->tachePrincipale = $tachePrincipale;

        return $this;
    }

    /**
     * Get tachePrincipale
     *
     * @return \AppBundle\Entity\Tache
     */
    public function getTachePrincipale()
    {
        return $this->tachePrincipale;
    }

    /**
     * Set tacheSuivante
     *
     * @param \AppBundle\Entity\Tache $tacheSuivante
     *
     * @return TacheSuivante
     */
    public function setTacheSuivante(\AppBundle\Entity\Tache $tacheSuivante = null)
    {
        $this->tacheSuivante = $tacheSuivante;

        return $this;
    }

    /**
     * Get tacheSuivante
     *
     * @return \AppBundle\Entity\Tache
     */
    public function getTacheSuivante()
    {
        return $this->tacheSuivante;
    }
}
