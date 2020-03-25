<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuIntranetOperateur
 *
 * @ORM\Table(name="menu_intranet_operateur", indexes={@ORM\Index(name="fk_mio_menuintid_idx", columns={"menu_intranet_id"}), @ORM\Index(name="fk_mio_accopid_idx", columns={"access_operateur_id"}), @ORM\Index(name="fk_mio_opid_idx", columns={"operateur_id"})})
 * @ORM\Entity
 */
class MenuIntranetOperateur
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
     *   @ORM\JoinColumn(name="operateur_id", referencedColumnName="id")
     * })
     */
    private $operateur;

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
     * Set operateur
     *
     * @param \AppBundle\Entity\Operateur $operateur
     *
     * @return MenuIntranetOperateur
     */
    public function setOperateur(\AppBundle\Entity\Operateur $operateur = null)
    {
        $this->operateur = $operateur;

        return $this;
    }

    /**
     * Get operateur
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getOperateur()
    {
        return $this->operateur;
    }

    /**
     * Set accessOperateur
     *
     * @param \AppBundle\Entity\AccesOperateur $accessOperateur
     *
     * @return MenuIntranetOperateur
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
     * @return MenuIntranetOperateur
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
