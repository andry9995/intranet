<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CleSouscategorie
 *
 * @ORM\Table(name="cle_souscategorie", uniqueConstraints={@ORM\UniqueConstraint(name="cle_UNIQUE", columns={"cle", "banque_id"})}, indexes={@ORM\Index(name="fk_cle_souscategorie1_idx", columns={"souscategorie_id"}), @ORM\Index(name="fk_cle_banque1_idx", columns={"banque_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CleSouscategorieRepository")
 */
class CleSouscategorie
{
    /**
     * @var string
     *
     * @ORM\Column(name="cle", type="string", length=45, nullable=false)
     */
    private $cle;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="type_recherche", type="integer", nullable=true)
     */
    private $typeRecherche;

    /**
     * @var \AppBundle\Entity\Souscategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Souscategorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="souscategorie_id", referencedColumnName="id")
     * })
     */
    private $souscategorie;

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
     * Set cle
     *
     * @param string $cle
     *
     * @return CleSouscategorie
     */
    public function setCle($cle)
    {
        $this->cle = $cle;

        return $this;
    }

    /**
     * Get cle
     *
     * @return string
     */
    public function getCle()
    {
        return $this->cle;
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
     * Set souscategorie
     *
     * @param \AppBundle\Entity\Souscategorie $souscategorie
     *
     * @return CleSouscategorie
     */
    public function setSouscategorie(\AppBundle\Entity\Souscategorie $souscategorie = null)
    {
        $this->souscategorie = $souscategorie;

        return $this;
    }

    /**
     * Get souscategorie
     *
     * @return \AppBundle\Entity\Souscategorie
     */
    public function getSouscategorie()
    {
        return $this->souscategorie;
    }

    /**
     * Set banque
     *
     * @param \AppBundle\Entity\Banque $banque
     *
     * @return CleSouscategorie
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

    /**
     * @param $typeRecherche
     * @return $this
     */
    public function setTypeRecherche($typeRecherche){
        $this->typeRecherche = $typeRecherche;
        return $this;
    }

    /**
     * @return int
     */
    public function getTypeRecherche(){
        return $this->typeRecherche;
    }
}
