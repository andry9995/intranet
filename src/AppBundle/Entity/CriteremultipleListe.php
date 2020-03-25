<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CriteremultipleListe
 *
 * @ORM\Table(name="criteremultiple_liste", indexes={@ORM\Index(name="fk_critere_liste_criteres1_idx", columns={"criteres_id"}), @ORM\Index(name="fk_critere_liste_pcc1_idx", columns={"pcc_id"})})
 * @ORM\Entity
 */
class CriteremultipleListe
{
    /**
     * @var string
     *
     * @ORM\Column(name="sens", type="string", length=1, nullable=true)
     */
    private $sens = 'D';

    /**
     * @var integer
     *
     * @ORM\Column(name="type_critere", type="integer", nullable=true)
     */
    private $typeCritere;

    /**
     * @var string
     *
     * @ORM\Column(name="critere", type="string", length=45, nullable=true)
     */
    private $critere;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Pcc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pcc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pcc_id", referencedColumnName="id")
     * })
     */
    private $pcc;

    /**
     * @var \AppBundle\Entity\Criteres
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Criteres")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="criteres_id", referencedColumnName="id")
     * })
     */
    private $criteres;



    /**
     * Set sens
     *
     * @param string $sens
     *
     * @return CriteremultipleListe
     */
    public function setSens($sens)
    {
        $this->sens = $sens;

        return $this;
    }

    /**
     * Get sens
     *
     * @return string
     */
    public function getSens()
    {
        return $this->sens;
    }

    /**
     * Set typeCritere
     *
     * @param integer $typeCritere
     *
     * @return CriteremultipleListe
     */
    public function setTypeCritere($typeCritere)
    {
        $this->typeCritere = $typeCritere;

        return $this;
    }

    /**
     * Get typeCritere
     *
     * @return integer
     */
    public function getTypeCritere()
    {
        return $this->typeCritere;
    }

    /**
     * Set critere
     *
     * @param string $critere
     *
     * @return CriteremultipleListe
     */
    public function setCritere($critere)
    {
        $this->critere = $critere;

        return $this;
    }

    /**
     * Get critere
     *
     * @return string
     */
    public function getCritere()
    {
        return $this->critere;
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
     * Set pcc
     *
     * @param \AppBundle\Entity\Pcc $pcc
     *
     * @return CriteremultipleListe
     */
    public function setPcc(\AppBundle\Entity\Pcc $pcc = null)
    {
        $this->pcc = $pcc;

        return $this;
    }

    /**
     * Get pcc
     *
     * @return \AppBundle\Entity\Pcc
     */
    public function getPcc()
    {
        return $this->pcc;
    }

    /**
     * Set criteres
     *
     * @param \AppBundle\Entity\Criteres $criteres
     *
     * @return CriteremultipleListe
     */
    public function setCriteres(\AppBundle\Entity\Criteres $criteres = null)
    {
        $this->criteres = $criteres;

        return $this;
    }

    /**
     * Get criteres
     *
     * @return \AppBundle\Entity\Criteres
     */
    public function getCriteres()
    {
        return $this->criteres;
    }
}
