<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DebSaisie1
 *
 * @ORM\Table(name="deb_saisie1")
 * @ORM\Entity
 */
class DebSaisie1
{
    /**
     * @var string
     *
     * @ORM\Column(name="cl", type="string", length=45, nullable=true)
     */
    private $cl;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set cl
     *
     * @param string $cl
     *
     * @return DebSaisie1
     */
    public function setCl($cl)
    {
        $this->cl = $cl;

        return $this;
    }

    /**
     * Get cl
     *
     * @return string
     */
    public function getCl()
    {
        return $this->cl;
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
