<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BanqueRemise
 *
 * @ORM\Table(name="banque_remise")
 * @ORM\Entity
 */
class BanqueRemise
{
    /**
     * @var \AppBundle\Entity\Image
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Image")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     * })
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="num_compte", type="string", length=50, nullable=true)
     */
    private $numCompte = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="nombre_cheque", type="integer", nullable=false)
     */
    private $nombreCheque;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_remise", type="date", nullable=true)
     */
    private $dateRemise;

    /**
     * @var string
     *
     * @ORM\Column(name="num_remise", type="string", length=50, nullable=true)
     */
    private $numRemise = '';

    /**
     * @var float
     *
     * @ORM\Column(name="total_cheque", type="float", precision=10, scale=0, nullable=true)
     */
    private $totalCheque = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set imageId
     *
     * @param Image $image
     *
     * @return BanqueRemise
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get imageId
     *
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set numCompte
     *
     * @param string $numCompte
     *
     * @return BanqueRemise
     */
    public function setNumCompte($numCompte)
    {
        $this->numCompte = $numCompte;

        return $this;
    }

    /**
     * Get numCompte
     *
     * @return string
     */
    public function getNumCompte()
    {
        return $this->numCompte;
    }

    /**
     * Set nombreCheque
     *
     * @param integer $nombreCheque
     *
     * @return BanqueRemise
     */
    public function setNombreCheque($nombreCheque)
    {
        $this->nombreCheque = $nombreCheque;

        return $this;
    }

    /**
     * Get nombreCheque
     *
     * @return integer
     */
    public function getNombreCheque()
    {
        return $this->nombreCheque;
    }

    /**
     * Set dateRemise
     *
     * @param \DateTime $dateRemise
     *
     * @return BanqueRemise
     */
    public function setDateRemise($dateRemise)
    {
        $this->dateRemise = $dateRemise;

        return $this;
    }

    /**
     * Get dateRemise
     *
     * @return \DateTime
     */
    public function getDateRemise()
    {
        return $this->dateRemise;
    }

    /**
     * Set numRemise
     *
     * @param string $numRemise
     *
     * @return BanqueRemise
     */
    public function setNumRemise($numRemise)
    {
        $this->numRemise = $numRemise;

        return $this;
    }

    /**
     * Get numRemise
     *
     * @return string
     */
    public function getNumRemise()
    {
        return $this->numRemise;
    }

    /**
     * Set totalCheque
     *
     * @param float $totalCheque
     *
     * @return BanqueRemise
     */
    public function setTotalCheque($totalCheque)
    {
        $this->totalCheque = $totalCheque;

        return $this;
    }

    /**
     * Get totalCheque
     *
     * @return float
     */
    public function getTotalCheque()
    {
        return $this->totalCheque;
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
