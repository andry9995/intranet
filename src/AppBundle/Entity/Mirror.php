<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mirror
 *
 * @ORM\Table(name="mirror")
 * @ORM\Entity
 */
class Mirror
{
    /**
     * @var string
     *
     * @ORM\Column(name="text", type="string", length=45, nullable=true)
     */
    private $text;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=45, nullable=true)
     */
    private $source;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set text
     *
     * @param string $text
     *
     * @return Mirror
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set source
     *
     * @param string $source
     *
     * @return Mirror
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
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
