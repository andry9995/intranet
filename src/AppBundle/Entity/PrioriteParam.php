<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrioriteParam
 *
 * @ORM\Table(name="priorite_param", uniqueConstraints={@ORM\UniqueConstraint(name="param_name_UNIQUE", columns={"param_name"})})
 * @ORM\Entity
 */
class PrioriteParam
{
    /**
     * @var string
     *
     * @ORM\Column(name="param_name", type="string", length=50, nullable=false)
     */
    private $paramName;

    /**
     * @var array
     *
     * @ORM\Column(name="param_value", type="array", nullable=true)
     */
    private $paramValue;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set paramName
     *
     * @param string $paramName
     *
     * @return PrioriteParam
     */
    public function setParamName($paramName)
    {
        $this->paramName = $paramName;

        return $this;
    }

    /**
     * Get paramName
     *
     * @return string
     */
    public function getParamName()
    {
        return $this->paramName;
    }

    /**
     * Set paramValue
     *
     * @param array $paramValue
     *
     * @return PrioriteParam
     */
    public function setParamValue($paramValue)
    {
        $this->paramValue = $paramValue;

        return $this;
    }

    /**
     * Get paramValue
     *
     * @return array
     */
    public function getParamValue()
    {
        return $this->paramValue;
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
