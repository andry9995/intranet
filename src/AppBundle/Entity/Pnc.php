<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pnc
 *
 * @ORM\Table(name="pnc", uniqueConstraints={@ORM\UniqueConstraint(name="rubrique_UNIQUE", columns={"rubrique"})})
 * @ORM\Entity
 */
class Pnc
{
    /**
     * @var string
     *
     * @ORM\Column(name="rubrique", type="string", length=100, nullable=false)
     */
    private $rubrique;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="code_champ", type="string", length=45, nullable=true)
     */
    private $codeChamp;

    /**
     * @var integer
     *
     * @ORM\Column(name="coche_auto", type="integer", nullable=true)
     */
    private $cocheAuto = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set rubrique
     *
     * @param string $rubrique
     *
     * @return Pnc
     */
    public function setRubrique($rubrique)
    {
        $this->rubrique = $rubrique;

        return $this;
    }

    /**
     * Get rubrique
     *
     * @return string
     */
    public function getRubrique()
    {
        return $this->rubrique;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Pnc
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set codeChamp
     *
     * @param string $codeChamp
     *
     * @return Pnc
     */
    public function setCodeChamp($codeChamp)
    {
        $this->codeChamp = $codeChamp;

        return $this;
    }

    /**
     * Get codeChamp
     *
     * @return string
     */
    public function getCodeChamp()
    {
        return $this->codeChamp;
    }

    /**
     * Set cocheAuto
     *
     * @param integer $cocheAuto
     *
     * @return Pnc
     */
    public function setCocheAuto($cocheAuto)
    {
        $this->cocheAuto = $cocheAuto;

        return $this;
    }

    /**
     * Get cocheAuto
     *
     * @return integer
     */
    public function getCocheAuto()
    {
        return $this->cocheAuto;
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
