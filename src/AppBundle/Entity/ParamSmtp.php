<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ParamSmtp
 *
 * @ORM\Table(name="param_smtp", indexes={@ORM\Index(name="fk_param_smtp_client1_idx", columns={"client_id"})})
 * @ORM\Entity
 */
class ParamSmtp
{
    /**
     * @var string
     *
     * @ORM\Column(name="smtp", type="string", length=50, nullable=false)
     */
    private $smtp;

    /**
     * @var integer
     *
     * @ORM\Column(name="port", type="integer", nullable=false)
     */
    private $port;

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
     * @ORM\Column(name="certificate", type="string", length=45, nullable=true)
     */
    private $certificate = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * Set smtp
     *
     * @param string $smtp
     *
     * @return ParamSmtp
     */
    public function setSmtp($smtp)
    {
        $this->smtp = $smtp;

        return $this;
    }

    /**
     * Get smtp
     *
     * @return string
     */
    public function getSmtp()
    {
        return $this->smtp;
    }

    /**
     * Set port
     *
     * @param integer $port
     *
     * @return ParamSmtp
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port
     *
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set login
     *
     * @param string $login
     *
     * @return ParamSmtp
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
     * @return ParamSmtp
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
     * Set certificate
     *
     * @param string $certificate
     *
     * @return ParamSmtp
     */
    public function setCertificate($certificate)
    {
        $this->certificate = $certificate;

        return $this;
    }

    /**
     * Get certificate
     *
     * @return string
     */
    public function getCertificate()
    {
        return $this->certificate;
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
     * Set client
     *
     * @param \AppBundle\Entity\Client $client
     *
     * @return ParamSmtp
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
}
