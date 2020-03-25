<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rattachement
 *
 * @ORM\Table(name="rattachement", indexes={@ORM\Index(name="fk_op_manag_idx", columns={"operateur_id"}), @ORM\Index(name="fk_oprat_idx", columns={"operateur_rat_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RattachementRepository")
 */
class Rattachement
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
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="operateur_rat_id", referencedColumnName="id")
     * })
     */
    private $operateurRat;

    /**
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="operateur_id", referencedColumnName="id")
     * })
     */
    private $operateur;



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
     * Set operateurRat
     *
     * @param \AppBundle\Entity\Operateur $operateurRat
     *
     * @return Rattachement
     */
    public function setOperateurRat(\AppBundle\Entity\Operateur $operateurRat = null)
    {
        $this->operateurRat = $operateurRat;

        return $this;
    }

    /**
     * Get operateurRat
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getOperateurRat()
    {
        return $this->operateurRat;
    }

    /**
     * Set operateur
     *
     * @param \AppBundle\Entity\Operateur $operateur
     *
     * @return Rattachement
     */
    public function setOperateur(\AppBundle\Entity\Operateur $operateur = null)
    {
        $this->operateur = $operateur;

        return $this;
    }

    /**
     * Get operateur
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getOperateur()
    {
        return $this->operateur;
    }
}
