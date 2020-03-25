<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProcessusMenuIntranet
 *
 * @ORM\Table(name="processus_menu_intranet", indexes={@ORM\Index(name="fk_procmenuintra_procid_idx", columns={"processus_id"}), @ORM\Index(name="fk_procmenuintra_menuid_idx", columns={"menu_intranet_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProcessusMenuIntranetRepository")
 */
class ProcessusMenuIntranet
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
     * @var \AppBundle\Entity\MenuIntranet
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MenuIntranet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="menu_intranet_id", referencedColumnName="id")
     * })
     */
    private $menuIntranet;

    /**
     * @var \AppBundle\Entity\Processus
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Processus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="processus_id", referencedColumnName="id")
     * })
     */
    private $processus;



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
     * @return ProcessusMenuIntranet
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

    /**
     * Set processus
     *
     * @param \AppBundle\Entity\Processus $processus
     *
     * @return ProcessusMenuIntranet
     */
    public function setProcessus(\AppBundle\Entity\Processus $processus = null)
    {
        $this->processus = $processus;

        return $this;
    }

    /**
     * Get processus
     *
     * @return \AppBundle\Entity\Processus
     */
    public function getProcessus()
    {
        return $this->processus;
    }
}
