<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuIntranetAccess
 *
 * @ORM\Table(name="menu_intranet_access", indexes={@ORM\Index(name="fk_miac_menuintraid_idx", columns={"menu_intranet_id"}), @ORM\Index(name="fk_miac_accesopid_idx", columns={"access_operateur_id"})})
 * @ORM\Entity
 */
class MenuIntranetAccess
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
     * @var \AppBundle\Entity\AccesOperateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AccesOperateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="access_operateur_id", referencedColumnName="id")
     * })
     */
    private $accessOperateur;

    /**
     * @var \AppBundle\Entity\MenuIntranet
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MenuIntranet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="menu_intranet_id", referencedColumnName="id")
     * })
     */
    private $menuIntranet;



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
     * Set accessOperateur
     *
     * @param \AppBundle\Entity\AccesOperateur $accessOperateur
     *
     * @return MenuIntranetAccess
     */
    public function setAccessOperateur(\AppBundle\Entity\AccesOperateur $accessOperateur = null)
    {
        $this->accessOperateur = $accessOperateur;

        return $this;
    }

    /**
     * Get accessOperateur
     *
     * @return \AppBundle\Entity\AccesOperateur
     */
    public function getAccessOperateur()
    {
        return $this->accessOperateur;
    }

    /**
     * Set menuIntranet
     *
     * @param \AppBundle\Entity\MenuIntranet $menuIntranet
     *
     * @return MenuIntranetAccess
     */
    public function setMenuIntranet(\AppBundle\Entity\MenuIntranet $menuIntranet = null)
    {
        $this->menuIntranet = $menuIntranet;

        return $this;
    }

    /**
     * Get menuIntranet
     *
     * @return \AppBundle\Entity\MenuIntranet
     */
    public function getMenuIntranet()
    {
        return $this->menuIntranet;
    }
}
