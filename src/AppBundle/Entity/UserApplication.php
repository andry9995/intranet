<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserApplication
 *
 * @ORM\Table(name="user_application", indexes={@ORM\Index(name="fk_user_application_idx", columns={"operateur_id"}), @ORM\Index(name="fk_user_application_traitement_idx", columns={"etape_traitement_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserApplicationRepository")
 */
class UserApplication
{
    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=20, nullable=false)
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="parametre", type="string", length=250, nullable=true)
     */
    private $parametre;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_jour", type="date", nullable=false)
     */
    private $dateJour;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\EtapeTraitement
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EtapeTraitement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="etape_traitement_id", referencedColumnName="id")
     * })
     */
    private $etapeTraitement;

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
     * Set ip
     *
     * @param string $ip
     *
     * @return UserApplication
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set parametre
     *
     * @param string $parametre
     *
     * @return UserApplication
     */
    public function setParametre($parametre)
    {
        $this->parametre = $parametre;

        return $this;
    }

    /**
     * Get parametre
     *
     * @return string
     */
    public function getParametre()
    {
        return $this->parametre;
    }

    /**
     * Set dateJour
     *
     * @param \DateTime $dateJour
     *
     * @return UserApplication
     */
    public function setDateJour($dateJour)
    {
        $this->dateJour = $dateJour;

        return $this;
    }

    /**
     * Get dateJour
     *
     * @return \DateTime
     */
    public function getDateJour()
    {
        return $this->dateJour;
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
     * Set etapeTraitement
     *
     * @param \AppBundle\Entity\EtapeTraitement $etapeTraitement
     *
     * @return UserApplication
     */
    public function setEtapeTraitement(\AppBundle\Entity\EtapeTraitement $etapeTraitement = null)
    {
        $this->etapeTraitement = $etapeTraitement;

        return $this;
    }

    /**
     * Get etapeTraitement
     *
     * @return \AppBundle\Entity\EtapeTraitement
     */
    public function getEtapeTraitement()
    {
        return $this->etapeTraitement;
    }

    /**
     * Set operateur
     *
     * @param \AppBundle\Entity\Operateur $operateur
     *
     * @return UserApplication
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
