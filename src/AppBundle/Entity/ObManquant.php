<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ObManquant
 *
 * @ORM\Table(name="ob_manquant", indexes={@ORM\Index(name="fk_ob_manquant_releve_idx", columns={"releve_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ObManquantRepository")
 */
class ObManquant
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
     * @var \AppBundle\Entity\Releve
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Releve")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="releve_id", referencedColumnName="id")
     * })
     */
    private $releve;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = 0;



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
     * Set releve
     *
     * @param \AppBundle\Entity\Releve $releve
     *
     * @return ObManquant
     */
    public function setReleve(\AppBundle\Entity\Releve $releve = null)
    {
        $this->releve = $releve;

        return $this;
    }

    /**
     * Get releve
     *
     * @return \AppBundle\Entity\Releve
     */
    public function getReleve()
    {
        return $this->releve;
    }


    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status){
        $this->status = $status;
        return  $this;
    }

    /**
     * @return int
     */
    public function getStatus(){
        return $this->status;
    }
}
