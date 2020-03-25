<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ParamEmailImage
 *
 * @ORM\Table(name="param_email_image", indexes={@ORM\Index(name="fk_param_email_image_dossier1_idx", columns={"dossier_id"})})
 * @ORM\Entity
 */
class ParamEmailImage
{
    /**
     * @var string
     *
     * @ORM\Column(name="destinataire", type="text", length=65535, nullable=false)
     */
    private $destinataire;

    /**
     * @var string
     *
     * @ORM\Column(name="contenu", type="text", length=65535, nullable=false)
     */
    private $contenu;

    /**
     * @var integer
     *
     * @ORM\Column(name="periode", type="integer", nullable=false)
     */
    private $periode;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="debut_envoi", type="date", nullable=false)
     */
    private $debutEnvoi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dernier_envoi", type="date", nullable=true)
     */
    private $dernierEnvoi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="prochain_envoi", type="date", nullable=false)
     */
    private $prochainEnvoi;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_contact", type="string", length=250, nullable=false)
     */
    private $nomContact;

    /**
     * @var integer
     *
     * @ORM\Column(name="actif", type="integer", nullable=false)
     */
    private $actif = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Dossier
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Dossier")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dossier_id", referencedColumnName="id")
     * })
     */
    private $dossier;



    /**
     * Set destinataire
     *
     * @param string $destinataire
     *
     * @return ParamEmailImage
     */
    public function setDestinataire($destinataire)
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    /**
     * Get destinataire
     *
     * @return string
     */
    public function getDestinataire()
    {
        return $this->destinataire;
    }

    /**
     * Set contenu
     *
     * @param string $contenu
     *
     * @return ParamEmailImage
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get contenu
     *
     * @return string
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Set periode
     *
     * @param integer $periode
     *
     * @return ParamEmailImage
     */
    public function setPeriode($periode)
    {
        $this->periode = $periode;

        return $this;
    }

    /**
     * Get periode
     *
     * @return integer
     */
    public function getPeriode()
    {
        return $this->periode;
    }

    /**
     * Set debutEnvoi
     *
     * @param \DateTime $debutEnvoi
     *
     * @return ParamEmailImage
     */
    public function setDebutEnvoi($debutEnvoi)
    {
        $this->debutEnvoi = $debutEnvoi;

        return $this;
    }

    /**
     * Get debutEnvoi
     *
     * @return \DateTime
     */
    public function getDebutEnvoi()
    {
        return $this->debutEnvoi;
    }

    /**
     * Set dernierEnvoi
     *
     * @param \DateTime $dernierEnvoi
     *
     * @return ParamEmailImage
     */
    public function setDernierEnvoi($dernierEnvoi)
    {
        $this->dernierEnvoi = $dernierEnvoi;

        return $this;
    }

    /**
     * Get dernierEnvoi
     *
     * @return \DateTime
     */
    public function getDernierEnvoi()
    {
        return $this->dernierEnvoi;
    }

    /**
     * Set prochainEnvoi
     *
     * @param \DateTime $prochainEnvoi
     *
     * @return ParamEmailImage
     */
    public function setProchainEnvoi($prochainEnvoi)
    {
        $this->prochainEnvoi = $prochainEnvoi;

        return $this;
    }

    /**
     * Get prochainEnvoi
     *
     * @return \DateTime
     */
    public function getProchainEnvoi()
    {
        return $this->prochainEnvoi;
    }

    /**
     * Set nomContact
     *
     * @param string $nomContact
     *
     * @return ParamEmailImage
     */
    public function setNomContact($nomContact)
    {
        $this->nomContact = $nomContact;

        return $this;
    }

    /**
     * Get nomContact
     *
     * @return string
     */
    public function getNomContact()
    {
        return $this->nomContact;
    }

    /**
     * Set actif
     *
     * @param integer $actif
     *
     * @return ParamEmailImage
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get actif
     *
     * @return integer
     */
    public function getActif()
    {
        return $this->actif;
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
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return ParamEmailImage
     */
    public function setDossier(\AppBundle\Entity\Dossier $dossier = null)
    {
        $this->dossier = $dossier;

        return $this;
    }

    /**
     * Get dossier
     *
     * @return \AppBundle\Entity\Dossier
     */
    public function getDossier()
    {
        return $this->dossier;
    }
}
