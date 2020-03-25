<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TachePrecedente
 *
 * @ORM\Table(name="tache_precedente", uniqueConstraints={@ORM\UniqueConstraint(name="unique_tache_principale_precedent", columns={"tache_precedente", "tache_principale"})}, indexes={@ORM\Index(name="fk_tache_tache_prec_principale_idx", columns={"tache_principale"}), @ORM\Index(name="IDX_E52AAACDE52AAACD", columns={"tache_precedente"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TachePrecedenteRepository")
 */
class TachePrecedente
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
     *   @ORM\JoinColumn(name="tache_precedente", referencedColumnName="id")
     * })
     */
    private $tachePrecedente;



    /**
     * Set ordre
     *
     * @param integer $ordre
     *
     * @return TachePrecedente
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
     * @return TachePrecedente
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
     * Set tachePrecedente
     *
     * @param \AppBundle\Entity\Tache $tachePrecedente
     *
     * @return TachePrecedente
     */
    public function setTachePrecedente(\AppBundle\Entity\Tache $tachePrecedente = null)
    {
        $this->tachePrecedente = $tachePrecedente;

        return $this;
    }

    /**
     * Get tachePrecedente
     *
     * @return \AppBundle\Entity\Tache
     */
    public function getTachePrecedente()
    {
        return $this->tachePrecedente;
    }
}
