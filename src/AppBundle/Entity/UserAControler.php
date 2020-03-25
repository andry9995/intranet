<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserAControler
 *
 * @ORM\Table(name="user_a_controler", indexes={@ORM\Index(name="fk_user_a_controler_operateur_id_idx", columns={"operateur_id"}), @ORM\Index(name="fk_user_a_controler_organisation_id_idx", columns={"organisation_id"}), @ORM\Index(name="fk_user_a_controler_operateur_cocher_id_idx", columns={"operateur_cocher_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserAControlerRepository")
 */
class UserAControler
{
    /**
     * @var integer
     *
     * @ORM\Column(name="a_controler", type="integer", nullable=true)
     */
    private $aControler = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Organisation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Organisation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     * })
     */
    private $organisation;

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
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="operateur_cocher_id", referencedColumnName="id")
     * })
     */
    private $operateurCocher;



    /**
     * Set aControler
     *
     * @param integer $aControler
     *
     * @return UserAControler
     */
    public function setAControler($aControler)
    {
        $this->aControler = $aControler;

        return $this;
    }

    /**
     * Get aControler
     *
     * @return integer
     */
    public function getAControler()
    {
        return $this->aControler;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return UserAControler
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
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
     * Set organisation
     *
     * @param \AppBundle\Entity\Organisation $organisation
     *
     * @return UserAControler
     */
    public function setOrganisation(\AppBundle\Entity\Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get organisation
     *
     * @return \AppBundle\Entity\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set operateur
     *
     * @param \AppBundle\Entity\Operateur $operateur
     *
     * @return UserAControler
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

    /**
     * Set operateurCocher
     *
     * @param \AppBundle\Entity\Operateur $operateurCocher
     *
     * @return UserAControler
     */
    public function setOperateurCocher(\AppBundle\Entity\Operateur $operateurCocher = null)
    {
        $this->operateurCocher = $operateurCocher;

        return $this;
    }

    /**
     * Get operateurCocher
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getOperateurCocher()
    {
        return $this->operateurCocher;
    }
}
