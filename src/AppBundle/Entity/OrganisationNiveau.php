<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrganisationNiveau
 *
 * @ORM\Table(name="organisation_niveau")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrganisationNiveauRepository")
 */
class OrganisationNiveau
{
    /**
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=255, nullable=false)
     */
    private $titre;

    /**
     * @var integer
     *
     * @ORM\Column(name="rang", type="integer", nullable=false)
     */
    private $rang = '1000';

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_poste", type="boolean", nullable=false)
     */
    private $isPoste = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set titre
     *
     * @param string $titre
     *
     * @return OrganisationNiveau
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set rang
     *
     * @param integer $rang
     *
     * @return OrganisationNiveau
     */
    public function setRang($rang)
    {
        $this->rang = $rang;

        return $this;
    }

    /**
     * Get rang
     *
     * @return integer
     */
    public function getRang()
    {
        return $this->rang;
    }

    /**
     * Set isPoste
     *
     * @param boolean $isPoste
     *
     * @return OrganisationNiveau
     */
    public function setIsPoste($isPoste)
    {
        $this->isPoste = $isPoste;

        return $this;
    }

    /**
     * Get isPoste
     *
     * @return boolean
     */
    public function getIsPoste()
    {
        return $this->isPoste;
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
