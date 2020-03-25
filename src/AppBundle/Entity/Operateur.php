<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Operateur
 *
 * @ORM\Table(name="operateur", indexes={@ORM\Index(name="fk_operateur_acces_idx", columns={"acces_operateur_id"}), @ORM\Index(name="fk_operateur_fonction_idx", columns={"fonction_id"}), @ORM\Index(name="fk_operateur_poste1_idx", columns={"poste_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OperateurRepository")
 */
class Operateur implements UserInterface, AdvancedUserInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=50, nullable=false)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=50, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=250, nullable=true)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=250, nullable=true)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="text", length=65535, nullable=true)
     */
    private $adresse;

    /**
     * @var string
     *
     * @ORM\Column(name="tel", type="string", length=45, nullable=true)
     */
    private $tel;

    /**
     * @var string
     *
     * @ORM\Column(name="sexe", type="string", length=1, nullable=true)
     */
    private $sexe;

    /**
     * @var \Datetime
     * @ORM\Column(name="date_entree", type="date", nullable=true)
     */
    private $dateEntree;

    /**
     * @var \Datetime
     * @ORM\Column(name="date_sortie", type="date", nullable=true)
     */
    private $dateSortie;

    /**
     * @var integer
     *
     * @ORM\Column(name="supprimer", type="integer", nullable=false)
     */
    private $supprimer = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="matricule", type="string", length=45, nullable=true)
     */
    private $matricule;

    /**
     * @var float
     *
     * @ORM\Column(name="coeff", type="float", nullable=false)
     */
    private $coeff;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @var integer
     *
     * @ORM\Column(name="affecter_dossier", type="integer", nullable=true)
     */
    private $affecterDossier = '0';
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Poste
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Poste")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="poste_id", referencedColumnName="id")
     * })
     */
    private $poste;

    /**
     * @var \AppBundle\Entity\Organisation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Organisation")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     * })
     */
    private $organisation;

    /**
     * @var \AppBundle\Entity\Fonction
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Fonction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fonction_id", referencedColumnName="id")
     * })
     */
    private $fonction;

    /**
     * @var \AppBundle\Entity\AccesOperateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AccesOperateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="acces_operateur_id", referencedColumnName="id")
     * })
     */
    private $accesOperateur;

    private $roles = array();

    /**
     * Operateur constructor.
     */
    public function __construct()
    {
        $this->roles[] = "ROLE_USER";
    }

    /**
     * Set login
     *
     * @param string $login
     *
     * @return Operateur
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
     * @return Operateur
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
     * Set nom
     *
     * @param string $nom
     *
     * @return Operateur
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
     * @return Operateur
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
     * Set adresse
     *
     * @param string $adresse
     *
     * @return Operateur
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse
     *
     * @return string
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set tel
     *
     * @param string $tel
     *
     * @return Operateur
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
     * Set sexe
     *
     * @param string $sexe
     *
     * @return Operateur
     */
    public function setSexe($sexe)
    {
        $this->sexe = $sexe;

        return $this;
    }

    /**
     * Get sexe
     *
     * @return string
     */
    public function getSexe()
    {
        return $this->sexe;
    }

    /**
     * Set supprimer
     *
     * @param integer $supprimer
     *
     * @return Operateur
     */
    public function setSupprimer($supprimer)
    {
        $this->supprimer = $supprimer;

        return $this;
    }

    /**
     * Get supprimer
     *
     * @return integer
     */
    public function getSupprimer()
    {
        return $this->supprimer;
    }

    /**
     * Set matricule
     *
     * @param string $matricule
     *
     * @return Operateur
     */
    public function setMatricule($matricule)
    {
        $this->matricule = $matricule;

        return $this;
    }

    /**
     * Get matricule
     *
     * @return string
     */
    public function getMatricule()
    {
        return $this->matricule;
    }

    /**
     * @param \Datetime $dateEntree
     * @return Operateur
     */
    public function setDateEntree($dateEntree)
    {
        $this->dateEntree = $dateEntree;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getDateEntree()
    {
        return $this->dateEntree;
    }

    /**
     * @param \Datetime $dateSortie
     * @return $this
     */
    public function setDateSortie($dateSortie)
    {
        $this->dateSortie = $dateSortie;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getDateSortie()
    {
        return $this->dateSortie;
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
     * Set poste
     *
     * @param Poste $poste
     *
     * @return Operateur
     */
    public function setPoste(Poste $poste = null)
    {
        $this->poste = $poste;

        return $this;
    }

    /**
     * Get poste
     *
     * @return \AppBundle\Entity\Poste
     */
    public function getPoste()
    {
        return $this->poste;
    }

    /**
     * Set $organisation
     *
     * @param Organisation|null $organisation
     * @return $this
     */
    public function setOrganisation(Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get $organisation
     *
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set fonction
     *
     * @param Fonction $fonction
     *
     * @return Operateur
     */
    public function setFonction(Fonction $fonction = null)
    {
        $this->fonction = $fonction;

        return $this;
    }

    /**
     * Get fonction
     *
     * @return \AppBundle\Entity\Fonction
     */
    public function getFonction()
    {
        return $this->fonction;
    }

    /**
     * Set accesOperateur
     *
     * @param AccesOperateur $accesOperateur
     *
     * @return Operateur
     */
    public function setAccesOperateur(AccesOperateur $accesOperateur = null)
    {
        $this->accesOperateur = $accesOperateur;

        return $this;
    }

    /**
     * Get accesOperateur
     *
     * @return \AppBundle\Entity\AccesOperateur
     */
    public function getAccesOperateur()
    {
        return $this->accesOperateur;
    }


    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        if ($this->getAccesOperateur()) {
            $this->roles[] = $this->getAccesOperateur()->getCode();
        }

        return $this->roles;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return '';
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->getLogin();
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        //TODO erase credentials
    }

    /**
     * Set coeff
     *
     * @param float $coeff
     *
     * @return Operateur
     */
    public function setCoeff($coeff)
    {
        $this->coeff = $coeff;

        return $this;
    }

    /**
     * Get coeff
     *
     * @return float
     */
    public function getCoeff()
    {
        return $this->coeff;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     *
     * @return Operateur
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

    public function getCapaciteReelle()
    {
        $coeff = $this->getCoeff();
        $capacite_poste = $this->getPoste() ? $this->getPoste()->getCapacite() : 0;
        return $capacite_poste * $coeff;
    }

    /**
     * Set affecterDossier
     *
     * @param integer $affecterDossier
     *
     * @return Operateur
     */
    public function setAffecterDossier($affecterDossier)
    {
        $this->affecterDossier = $affecterDossier;

        return $this;
    }

    /**
     * Get affecterDossier
     *
     * @return integer
     */
    public function getAffecterDossier()
    {
        return $this->affecterDossier;
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return $this->supprimer == 0;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return true;
    }
}
