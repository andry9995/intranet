<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategorieAnc
 *
 * @ORM\Table(name="categorie_anc", uniqueConstraints={@ORM\UniqueConstraint(name="libelle_UNIQUE", columns={"libelle"})}, indexes={@ORM\Index(name="fk_categorie_journal1_idx", columns={"journal_id"})})
 * @ORM\Entity
 */
class CategorieAnc
{
    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=150, nullable=false)
     */
    private $libelle = '';

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=25, nullable=true)
     */
    private $code = '';

    /**
     * @var boolean
     *
     * @ORM\Column(name="afficher", type="boolean", nullable=false)
     */
    private $afficher = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="alias", type="string", length=20, nullable=false)
     */
    private $alias = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Journal
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Journal")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="journal_id", referencedColumnName="id")
     * })
     */
    private $journal;



    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return CategorieAnc
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
     * Set code
     *
     * @param string $code
     *
     * @return CategorieAnc
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set afficher
     *
     * @param boolean $afficher
     *
     * @return CategorieAnc
     */
    public function setAfficher($afficher)
    {
        $this->afficher = $afficher;

        return $this;
    }

    /**
     * Get afficher
     *
     * @return boolean
     */
    public function getAfficher()
    {
        return $this->afficher;
    }

    /**
     * Set alias
     *
     * @param string $alias
     *
     * @return CategorieAnc
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
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
     * Set journal
     *
     * @param \AppBundle\Entity\Journal $journal
     *
     * @return CategorieAnc
     */
    public function setJournal(\AppBundle\Entity\Journal $journal = null)
    {
        $this->journal = $journal;

        return $this;
    }

    /**
     * Get journal
     *
     * @return \AppBundle\Entity\Journal
     */
    public function getJournal()
    {
        return $this->journal;
    }
}
