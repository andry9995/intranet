<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TdNdfBilanPcc
 *
 * @ORM\Table(name="td_ndf_bilan_pcc", indexes={@ORM\Index(name="fk_td_ndf_bilan_pcc_pcc1_idx", columns={"pcc_id"}), @ORM\Index(name="fk_td_ndf_bilan_pcc_dossier1_idx", columns={"dossier_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TdNdfBilanPccRepository")
 */
class TdNdfBilanPcc
{
    /**
     * @var integer
     *
     * @ORM\Column(name="type_compte", type="integer", nullable=false)
     */
    private $typeCompte;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
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
     * @var \AppBundle\Entity\Dossier
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Dossier")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dossier_id", referencedColumnName="id")
     * })
     */
    private $dossier;



    /**
     * Set typeCompte
     *
     * @param integer $typeCompte
     *
     * @return TdNdfBilanPcc
     */
    public function setTypeCompte($typeCompte)
    {
        $this->typeCompte = $typeCompte;

        return $this;
    }

    /**
     * Get typeCompte
     *
     * @return integer
     */
    public function getTypeCompte()
    {
        return $this->typeCompte;
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
     * @return TdNdfBilanPcc
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
     * Set dossier
     *
     * @param \AppBundle\Entity\Dossier $dossier
     *
     * @return TdNdfBilanPcc
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
