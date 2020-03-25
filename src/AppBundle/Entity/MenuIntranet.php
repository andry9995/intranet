<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * MenuIntranet
 *
 * @ORM\Table(name="menu_intranet", indexes={@ORM\Index(name="fk_menu_intranet_menu1_idx", columns={"menu_intranet_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MenuIntranetRepository")
 */
class MenuIntranet
{
    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=50, nullable=false)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="lien", type="string", length=255, nullable=false)
     */
    private $lien;

    /**
     * @var integer
     *
     * @ORM\Column(name="rang", type="integer", nullable=false)
     */
    private $rang = '1000';

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=45, nullable=false)
     */
    private $icon = '';

    /**
     * @var string
     *
     * @ORM\Column(name="background_color", type="string", length=45, nullable=true)
     */
    private $backgroundColor;

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=45, nullable=true)
     */
    private $class;

    /**
     * @var string
     *
     * @ORM\Column(name="parametre", type="string", length=200, nullable=true)
     */
    private $parametre;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * Set libelle
     *
     * @param string $libelle
     *
     * @return MenuIntranet
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
     * Set lien
     *
     * @param string $lien
     *
     * @return MenuIntranet
     */
    public function setLien($lien)
    {
        $this->lien = $lien;

        return $this;
    }

    /**
     * Get lien
     *
     * @return string
     */
    public function getLien()
    {
        return $this->lien;
    }

    /**
     * Set rang
     *
     * @param integer $rang
     *
     * @return MenuIntranet
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
     * Set icon
     *
     * @param string $icon
     *
     * @return MenuIntranet
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set backgroundColor
     *
     * @param string $backgroundColor
     *
     * @return MenuIntranet
     */
    public function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * Get backgroundColor
     *
     * @return string
     */
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * Set class
     *
     * @param string $class
     *
     * @return MenuIntranet
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set parametre
     *
     * @param string $parametre
     *
     * @return MenuIntranet
     */
    public function setParametre($parametre)
    {
        $this->parametre = $parametre;

        return $this;
    }

    /**
     * Get parametre
     *
     * @return string
     */
    public function getParametre()
    {
        return $this->parametre;
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
     * Set menuIntranet
     *
     * @param \AppBundle\Entity\MenuIntranet $menuIntranet
     *
     * @return MenuIntranet
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

    private $childs = array();

    /**
     * set menu childs
     *
     * @param array $childs
     * @return $this
     */
    public function setChild(Array $childs)
    {
        foreach ($childs as $submenu) {
            $this->childs[] = $submenu;
        }
        return $this;
    }

    /**
     * Get menu child
     *
     * @return array()
     */
    public function getChild()
    {
        return $this->childs;
    }

    public function clearChildren() {
        $this->childs = new ArrayCollection();
    }
}
