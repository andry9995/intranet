<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ParamDatePonctuel
 *
 * @ORM\Table(name="param_date_ponctuel", indexes={@ORM\Index(name="fk_param_date_ponctuel_param_email_image1_idx", columns={"param_email_image_id"})})
 * @ORM\Entity
 */
class ParamDatePonctuel
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pdate", type="date", nullable=false)
     */
    private $pdate;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\ParamEmailImage
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ParamEmailImage")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="param_email_image_id", referencedColumnName="id")
     * })
     */
    private $paramEmailImage;



    /**
     * Set pdate
     *
     * @param \DateTime $pdate
     *
     * @return ParamDatePonctuel
     */
    public function setPdate($pdate)
    {
        $this->pdate = $pdate;

        return $this;
    }

    /**
     * Get pdate
     *
     * @return \DateTime
     */
    public function getPdate()
    {
        return $this->pdate;
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
     * Set paramEmailImage
     *
     * @param \AppBundle\Entity\ParamEmailImage $paramEmailImage
     *
     * @return ParamDatePonctuel
     */
    public function setParamEmailImage(\AppBundle\Entity\ParamEmailImage $paramEmailImage = null)
    {
        $this->paramEmailImage = $paramEmailImage;

        return $this;
    }

    /**
     * Get paramEmailImage
     *
     * @return \AppBundle\Entity\ParamEmailImage
     */
    public function getParamEmailImage()
    {
        return $this->paramEmailImage;
    }
}
