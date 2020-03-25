<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TdTvaPcc
 *
 * @ORM\Table(name="td_tva_pcc", indexes={@ORM\Index(name="fk_td_pcc_pcc1_idx", columns={"pcc_id"}), @ORM\Index(name="fk_td_pcc_tva_taux1_idx", columns={"tva_taux_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TdTvaPccRepository")
 */
class TdTvaPcc
{
    /**
     * @var integer
     *
     * @ORM\Column(name="type_caisse", type="integer", nullable=true)
     */
    private $typeCaisse = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\TvaTaux
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TvaTaux")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tva_taux_id", referencedColumnName="id")
     * })
     */
    private $tvaTaux;

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
     * Set typeCaisse
     *
     * @param integer $typeCaisse
     *
     * @return TdTvaPcc
     */
    public function setTypeCaisse($typeCaisse)
    {
        $this->typeCaisse = $typeCaisse;

        return $this;
    }

    /**
     * Get typeCaisse
     *
     * @return integer
     */
    public function getTypeCaisse()
    {
        return $this->typeCaisse;
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
     * Set tvaTaux
     *
     * @param \AppBundle\Entity\TvaTaux $tvaTaux
     *
     * @return TdTvaPcc
     */
    public function setTvaTaux(\AppBundle\Entity\TvaTaux $tvaTaux = null)
    {
        $this->tvaTaux = $tvaTaux;

        return $this;
    }

    /**
     * Get tvaTaux
     *
     * @return \AppBundle\Entity\TvaTaux
     */
    public function getTvaTaux()
    {
        return $this->tvaTaux;
    }

    /**
     * Set pcc
     *
     * @param \AppBundle\Entity\Pcc $pcc
     *
     * @return TdTvaPcc
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
}
