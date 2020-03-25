<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ville
 *
 * @ORM\Table(name="ville")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VilleRepository")
 */
class Ville
{
    /**
     * @var string
     *
     * @ORM\Column(name="insee", type="string", length=45, nullable=true)
     */
    private $insee;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=45, nullable=true)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="code_postal", type="string", length=45, nullable=true)
     */
    private $codePostal;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=45, nullable=true)
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=45, nullable=true)
     */
    private $latitude;

    /**
     * @var integer
     *
     * @ORM\Column(name="pop99", type="integer", nullable=true)
     */
    private $pop99;

    /**
     * @var float
     *
     * @ORM\Column(name="surface", type="float", precision=10, scale=0, nullable=true)
     */
    private $surface;

    /**
     * @var integer
     *
     * @ORM\Column(name="altitude", type="integer", nullable=true)
     */
    private $altitude;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set insee
     *
     * @param string $insee
     *
     * @return Ville
     */
    public function setInsee($insee)
    {
        $this->insee = $insee;

        return $this;
    }

    /**
     * Get insee
     *
     * @return string
     */
    public function getInsee()
    {
        return $this->insee;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Ville
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
     * Set codePostal
     *
     * @param string $codePostal
     *
     * @return Ville
     */
    public function setCodePostal($codePostal)
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * Get codePostal
     *
     * @return string
     */
    public function getCodePostal()
    {
        return $this->codePostal;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return Ville
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     *
     * @return Ville
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set pop99
     *
     * @param integer $pop99
     *
     * @return Ville
     */
    public function setPop99($pop99)
    {
        $this->pop99 = $pop99;

        return $this;
    }

    /**
     * Get pop99
     *
     * @return integer
     */
    public function getPop99()
    {
        return $this->pop99;
    }

    /**
     * Set surface
     *
     * @param float $surface
     *
     * @return Ville
     */
    public function setSurface($surface)
    {
        $this->surface = $surface;

        return $this;
    }

    /**
     * Get surface
     *
     * @return float
     */
    public function getSurface()
    {
        return $this->surface;
    }

    /**
     * Set altitude
     *
     * @param integer $altitude
     *
     * @return Ville
     */
    public function setAltitude($altitude)
    {
        $this->altitude = $altitude;

        return $this;
    }

    /**
     * Get altitude
     *
     * @return integer
     */
    public function getAltitude()
    {
        return $this->altitude;
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
