<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IndicateurOperande
 *
 * @ORM\Table(name="indicateur_operande", indexes={@ORM\Index(name="fk_indicateur_operande_indicateur1_idx", columns={"indicateur_id"}), @ORM\Index(name="fk_indicateur_operande_rubrique1_idx", columns={"rubrique_id"}), @ORM\Index(name="fk_indicateur_operande_indicateur_cell1_idx", columns={"indicateur_cell_id"})})
 * @ORM\Entity
 */
class IndicateurOperande
{
    /**
     * @var integer
     *
     * @ORM\Column(name="variation_n", type="integer", nullable=false)
     */
    private $variationN = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Rubrique
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Rubrique")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rubrique_id", referencedColumnName="id")
     * })
     */
    private $rubrique;

    /**
     * @var \AppBundle\Entity\IndicateurCell
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\IndicateurCell")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="indicateur_cell_id", referencedColumnName="id")
     * })
     */
    private $indicateurCell;

    /**
     * @var \AppBundle\Entity\Indicateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Indicateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="indicateur_id", referencedColumnName="id")
     * })
     */
    private $indicateur;



    /**
     * Set variationN
     *
     * @param integer $variationN
     *
     * @return IndicateurOperande
     */
    public function setVariationN($variationN)
    {
        $this->variationN = $variationN;

        return $this;
    }

    /**
     * Get variationN
     *
     * @return integer
     */
    public function getVariationN()
    {
        return $this->variationN;
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
     * Set rubrique
     *
     * @param \AppBundle\Entity\Rubrique $rubrique
     *
     * @return IndicateurOperande
     */
    public function setRubrique(\AppBundle\Entity\Rubrique $rubrique = null)
    {
        $this->rubrique = $rubrique;

        return $this;
    }

    /**
     * Get rubrique
     *
     * @return \AppBundle\Entity\Rubrique
     */
    public function getRubrique()
    {
        return $this->rubrique;
    }

    /**
     * Set indicateurCell
     *
     * @param \AppBundle\Entity\IndicateurCell $indicateurCell
     *
     * @return IndicateurOperande
     */
    public function setIndicateurCell(\AppBundle\Entity\IndicateurCell $indicateurCell = null)
    {
        $this->indicateurCell = $indicateurCell;

        return $this;
    }

    /**
     * Get indicateurCell
     *
     * @return \AppBundle\Entity\IndicateurCell
     */
    public function getIndicateurCell()
    {
        return $this->indicateurCell;
    }

    /**
     * Set indicateur
     *
     * @param \AppBundle\Entity\Indicateur $indicateur
     *
     * @return IndicateurOperande
     */
    public function setIndicateur(\AppBundle\Entity\Indicateur $indicateur = null)
    {
        $this->indicateur = $indicateur;

        return $this;
    }

    /**
     * Get indicateur
     *
     * @return \AppBundle\Entity\Indicateur
     */
    public function getIndicateur()
    {
        return $this->indicateur;
    }
}
