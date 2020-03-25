<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuIntranetPoste
 *
 * @ORM\Table(name="menu_intranet_poste", indexes={@ORM\Index(name="fk_miposte_menuid_idx", columns={"menu_intranet_id"}), @ORM\Index(name="fk_miposte_orgid_idx", columns={"organisation_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MenuIntranetPosteRepository")
 */
class MenuIntranetPoste
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
     * @var \AppBundle\Entity\Organisation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Organisation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     * })
     */
    private $organisation;

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
     * Set organisation
     *
     * @param \AppBundle\Entity\Organisation $organisation
     *
     * @return MenuIntranetPoste
     */
    public function setOrganisation(\AppBundle\Entity\Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get organisation
     *
     * @return \AppBundle\Entity\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set menuIntranet
     *
     * @param \AppBundle\Entity\MenuIntranet $menuIntranet
     *
     * @return MenuIntranetPoste
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
