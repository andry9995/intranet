<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TdCaisseBilanPcc
 *
 * @ORM\Table(name="td_caisse_bilan_pcc", uniqueConstraints={@ORM\UniqueConstraint(name="unique", columns={"dossier_id", "type_caisse"})}, indexes={@ORM\Index(name="fk_td_caisse_bilan_pcc1_idx", columns={"pcc_id"}), @ORM\Index(name="fk_td_caisse_bilan_pcc_dossier1_idx", columns={"dossier_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TdCaisseBilanPccRepository")
 */
class TdCaisseBilanPcc
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
     * @var \AppBundle\Entity\Dossier
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Dossier")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dossier_id", referencedColumnName="id")
     * })
     */
    private $dossier;

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
     * @return TdCaisseBilanPcc
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
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return TdCaisseBilanPcc
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

    /**
     * Set pcc
     *
     * @param \AppBundle\Entity\Pcc $pcc
     *
     * @return TdCaisseBilanPcc
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
