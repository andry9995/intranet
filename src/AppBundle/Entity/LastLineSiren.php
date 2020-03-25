<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LastLineSiren
 *
 * @ORM\Table(name="last_line_siren")
 * @ORM\Entity
 */
class LastLineSiren
{
    /**
     * @var integer
     *
     * @ORM\Column(name="last_line", type="integer", nullable=true)
     */
    private $lastLine;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set lastLine
     *
     * @param integer $lastLine
     *
     * @return LastLineSiren
     */
    public function setLastLine($lastLine)
    {
        $this->lastLine = $lastLine;

        return $this;
    }

    /**
     * Get lastLine
     *
     * @return integer
     */
    public function getLastLine()
    {
        return $this->lastLine;
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
