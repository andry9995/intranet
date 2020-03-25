<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SoussouscategorieOrganisme
 *
 * @ORM\Table(name="soussouscategorie_organisme", indexes={@ORM\Index(name="fk_sscateg_org_sscid_idx", columns={"soussouscateg_id"}), @ORM\Index(name="fk_sscateg_org_orgid_idx", columns={"organisme_id"})})
 * @ORM\Entity
 */
class SoussouscategorieOrganisme
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
     * @var \AppBundle\Entity\Soussouscategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Soussouscategorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="soussouscateg_id", referencedColumnName="id")
     * })
     */
    private $soussouscateg;

    /**
     * @var \AppBundle\Entity\Organisme
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Organisme")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisme_id", referencedColumnName="id")
     * })
     */
    private $organisme;



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
     * Set soussouscateg
     *
     * @param \AppBundle\Entity\Soussouscategorie $soussouscateg
     *
     * @return SoussouscategorieOrganisme
     */
    public function setSoussouscateg(\AppBundle\Entity\Soussouscategorie $soussouscateg = null)
    {
        $this->soussouscateg = $soussouscateg;

        return $this;
    }

    /**
     * Get soussouscateg
     *
     * @return \AppBundle\Entity\Soussouscategorie
     */
    public function getSoussouscateg()
    {
        return $this->soussouscateg;
    }

    /**
     * Set organisme
     *
     * @param \AppBundle\Entity\Organisme $organisme
     *
     * @return SoussouscategorieOrganisme
     */
    public function setOrganisme(\AppBundle\Entity\Organisme $organisme = null)
    {
        $this->organisme = $organisme;

        return $this;
    }

    /**
     * Get organisme
     *
     * @return \AppBundle\Entity\Organisme
     */
    public function getOrganisme()
    {
        return $this->organisme;
    }
}
