<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LotUserGroup
 *
 * @ORM\Table(name="lot_user_group", indexes={@ORM\Index(name="fkusergroupid_idx", columns={"usergroup_id"}), @ORM\Index(name="fklotusergroup_lotid_idx", columns={"lot_id"}), @ORM\Index(name="fklotusergroup_categid_idx", columns={"catagorie_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LotUserGroupRepository")
 */
class LotUserGroup
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usergroup_id", referencedColumnName="id")
     * })
     */
    private $usergroup;

    /**
     * @var \AppBundle\Entity\Lot
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Lot")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     * })
     */
    private $lot;

    /**
     * @var \AppBundle\Entity\Categorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Categorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="catagorie_id", referencedColumnName="id")
     * })
     */
    private $catagorie;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_panier", type="date", nullable=false)
     */
    private $datePanier;


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
     * Set usergroup
     *
     * @param \AppBundle\Entity\Operateur $usergroup
     *
     * @return LotUserGroup
     */
    public function setUsergroup(\AppBundle\Entity\Operateur $usergroup = null)
    {
        $this->usergroup = $usergroup;

        return $this;
    }

    /**
     * Get usergroup
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getUsergroup()
    {
        return $this->usergroup;
    }

    /**
     * Set lot
     *
     * @param \AppBundle\Entity\Lot $lot
     *
     * @return LotUserGroup
     */
    public function setLot(\AppBundle\Entity\Lot $lot = null)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * Get lot
     *
     * @return \AppBundle\Entity\Lot
     */
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * Set categorie
     *
     * @param \AppBundle\Entity\Categorie $categorie
     *
     * @return LotUserGroup
     */
    public function setCatagorie(\AppBundle\Entity\Categorie $categorie = null)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return \AppBundle\Entity\Categorie
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return LotUserGroup
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
     * Set datePanier
     *
     * @param \DateTime $datePanier
     *
     * @return LotUserGroup
     */
    public function setDatePanier($datePanier)
    {
        $this->datePanier = $datePanier;

        return $this;
    }

    /**
     * Get datePanier
     *
     * @return \DateTime
     */
    public function getDatePanier()
    {
        return $this->datePanier;
    }
}
