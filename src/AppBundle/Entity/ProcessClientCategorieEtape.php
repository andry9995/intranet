<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProcessClientCategorieEtape
 *
 * @ORM\Table(name="process_client_categorie_etape", indexes={@ORM\Index(name="fk_pcce_etapetraitid_idx", columns={"etape_traitement_id"}), @ORM\Index(name="fk_pcce_processclicategid_idx", columns={"process_client_categ_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProcessClientCategorieEtapeRepository")
 */
class ProcessClientCategorieEtape
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\EtapeTraitement
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EtapeTraitement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="etape_traitement_id", referencedColumnName="id")
     * })
     */
    private $etapeTraitement;

    /**
     * @var \AppBundle\Entity\ProcessClientCategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ProcessClientCategorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="process_client_categ_id", referencedColumnName="id")
     * })
     */
    private $processClientCateg;



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
     * Set etapeTraitement
     *
     * @param \AppBundle\Entity\EtapeTraitement $etapeTraitement
     *
     * @return ProcessClientCategorieEtape
     */
    public function setEtapeTraitement(\AppBundle\Entity\EtapeTraitement $etapeTraitement = null)
    {
        $this->etapeTraitement = $etapeTraitement;

        return $this;
    }

    /**
     * Get etapeTraitement
     *
     * @return \AppBundle\Entity\EtapeTraitement
     */
    public function getEtapeTraitement()
    {
        return $this->etapeTraitement;
    }

    /**
     * Set processClientCateg
     *
     * @param \AppBundle\Entity\ProcessClientCategorie $processClientCateg
     *
     * @return ProcessClientCategorieEtape
     */
    public function setProcessClientCateg(\AppBundle\Entity\ProcessClientCategorie $processClientCateg = null)
    {
        $this->processClientCateg = $processClientCateg;

        return $this;
    }

    /**
     * Get processClientCateg
     *
     * @return \AppBundle\Entity\ProcessClientCategorie
     */
    public function getProcessClientCateg()
    {
        return $this->processClientCateg;
    }
}
