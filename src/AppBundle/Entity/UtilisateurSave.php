<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UtilisateurSave
 *
 * @ORM\Table(name="utilisateur_save", uniqueConstraints={@ORM\UniqueConstraint(name="login_UNIQUE", columns={"login"}), @ORM\UniqueConstraint(name="email_UNIQUE", columns={"email"})}, indexes={@ORM\Index(name="fk_utilisateur_save_type_utilisateur1_idx", columns={"type_utilisateur_id"}), @ORM\Index(name="fk_utilisateur_save_acces_utilisateur1_idx", columns={"acces_utilisateur_id"}), @ORM\Index(name="fk_utilisateur_save_client1_idx", columns={"client_id"})})
 * @ORM\Entity
 */
class UtilisateurSave
{
    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=45, nullable=false)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=45, nullable=true)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=50, nullable=false)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="text", length=65535, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="tel", type="string", length=20, nullable=true)
     */
    private $tel;

    /**
     * @var string
     *
     * @ORM\Column(name="skype", type="string", length=50, nullable=true)
     */
    private $skype;

    /**
     * @var boolean
     *
     * @ORM\Column(name="supprimer", type="boolean", nullable=false)
     */
    private $supprimer = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="string", length=250, nullable=true)
     */
    private $photo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="password_request_date", type="date", nullable=true)
     */
    private $passwordRequestDate;

    /**
     * @var string
     *
     * @ORM\Column(name="password_request_token", type="text", length=65535, nullable=true)
     */
    private $passwordRequestToken;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\TypeUtilisateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TypeUtilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_utilisateur_id", referencedColumnName="id")
     * })
     */
    private $typeUtilisateur;

    /**
     * @var \AppBundle\Entity\Client
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     * })
     */
    private $client;

    /**
     * @var \AppBundle\Entity\AccesUtilisateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AccesUtilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="acces_utilisateur_id", referencedColumnName="id")
     * })
     */
    private $accesUtilisateur;



    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return UtilisateurSave
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     *
     * @return UtilisateurSave
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return UtilisateurSave
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set login
     *
     * @param string $login
     *
     * @return UtilisateurSave
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return UtilisateurSave
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set tel
     *
     * @param string $tel
     *
     * @return UtilisateurSave
     */
    public function setTel($tel)
    {
        $this->tel = $tel;

        return $this;
    }

    /**
     * Get tel
     *
     * @return string
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * Set skype
     *
     * @param string $skype
     *
     * @return UtilisateurSave
     */
    public function setSkype($skype)
    {
        $this->skype = $skype;

        return $this;
    }

    /**
     * Get skype
     *
     * @return string
     */
    public function getSkype()
    {
        return $this->skype;
    }

    /**
     * Set supprimer
     *
     * @param boolean $supprimer
     *
     * @return UtilisateurSave
     */
    public function setSupprimer($supprimer)
    {
        $this->supprimer = $supprimer;

        return $this;
    }

    /**
     * Get supprimer
     *
     * @return boolean
     */
    public function getSupprimer()
    {
        return $this->supprimer;
    }

    /**
     * Set photo
     *
     * @param string $photo
     *
     * @return UtilisateurSave
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     *
     * @return UtilisateurSave
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set passwordRequestDate
     *
     * @param \DateTime $passwordRequestDate
     *
     * @return UtilisateurSave
     */
    public function setPasswordRequestDate($passwordRequestDate)
    {
        $this->passwordRequestDate = $passwordRequestDate;

        return $this;
    }

    /**
     * Get passwordRequestDate
     *
     * @return \DateTime
     */
    public function getPasswordRequestDate()
    {
        return $this->passwordRequestDate;
    }

    /**
     * Set passwordRequestToken
     *
     * @param string $passwordRequestToken
     *
     * @return UtilisateurSave
     */
    public function setPasswordRequestToken($passwordRequestToken)
    {
        $this->passwordRequestToken = $passwordRequestToken;

        return $this;
    }

    /**
     * Get passwordRequestToken
     *
     * @return string
     */
    public function getPasswordRequestToken()
    {
        return $this->passwordRequestToken;
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
     * Set typeUtilisateur
     *
     * @param \AppBundle\Entity\TypeUtilisateur $typeUtilisateur
     *
     * @return UtilisateurSave
     */
    public function setTypeUtilisateur(\AppBundle\Entity\TypeUtilisateur $typeUtilisateur = null)
    {
        $this->typeUtilisateur = $typeUtilisateur;

        return $this;
    }

    /**
     * Get typeUtilisateur
     *
     * @return \AppBundle\Entity\TypeUtilisateur
     */
    public function getTypeUtilisateur()
    {
        return $this->typeUtilisateur;
    }

    /**
     * Set client
     *
     * @param \AppBundle\Entity\Client $client
     *
     * @return UtilisateurSave
     */
    public function setClient(\AppBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \AppBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set accesUtilisateur
     *
     * @param \AppBundle\Entity\AccesUtilisateur $accesUtilisateur
     *
     * @return UtilisateurSave
     */
    public function setAccesUtilisateur(\AppBundle\Entity\AccesUtilisateur $accesUtilisateur = null)
    {
        $this->accesUtilisateur = $accesUtilisateur;

        return $this;
    }

    /**
     * Get accesUtilisateur
     *
     * @return \AppBundle\Entity\AccesUtilisateur
     */
    public function getAccesUtilisateur()
    {
        return $this->accesUtilisateur;
    }
}
