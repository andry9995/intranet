<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReleveEclatee
 *
 * @ORM\Table(name="releve_eclatee")
 * @ORM\Entity
 */
class ReleveEclatee
{
    /**
     * @var integer
     *
     * @ORM\Column(name="releve_id", type="integer", nullable=true)
     */
    private $releveId;

    /**
     * @var string
     *
     * @ORM\Column(name="releve_eclateecol", type="string", length=45, nullable=true)
     */
    private $releveEclateecol;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set releveId
     *
     * @param integer $releveId
     *
     * @return ReleveEclatee
     */
    public function setReleveId($releveId)
    {
        $this->releveId = $releveId;

        return $this;
    }

    /**
     * Get releveId
     *
     * @return integer
     */
    public function getReleveId()
    {
        return $this->releveId;
    }

    /**
     * Set releveEclateecol
     *
     * @param string $releveEclateecol
     *
     * @return ReleveEclatee
     */
    public function setReleveEclateecol($releveEclateecol)
    {
        $this->releveEclateecol = $releveEclateecol;

        return $this;
    }

    /**
     * Get releveEclateecol
     *
     * @return string
     */
    public function getReleveEclateecol()
    {
        return $this->releveEclateecol;
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
