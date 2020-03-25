<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProcessDossierCategorieEtape
 *
 * @ORM\Table(name="process_dossier_categorie_etape", indexes={@ORM\Index(name="fk_pdcat_etapid_idx", columns={"etape_traitement_id"}), @ORM\Index(name="fk_pdcat_procdoss_idx", columns={"process_dossier_categ_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProcessDossierCategorieEtapeRepository")
 */
class ProcessDossierCategorieEtape
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
     * @var \AppBundle\Entity\ProcessDossierCategorie
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ProcessDossierCategorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="process_dossier_categ_id", referencedColumnName="id")
     * })
     */
    private $processDossierCateg;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set processDossierCateg
     *
     * @param \AppBundle\Entity\ProcessDossierCategorie $processDossierCateg
     *
     * @return ProcessDossierCategorieEtape
     */
    public function setProcessDossierCateg(\AppBundle\Entity\ProcessDossierCategorie $processDossierCateg = null)
    {
        $this->processDossierCateg = $processDossierCateg;

        return $this;
    }

    /**
     * Get processDossierCateg
     *
     * @return \AppBundle\Entity\ProcessDossierCategorie
     */
    public function getProcessDossierCateg()
    {
        return $this->processDossierCateg;
    }

    /**
     * Set etapeTraitement
     *
     * @param \AppBundle\Entity\EtapeTraitement $etapeTraitement
     *
     * @return ProcessDossierCategorieEtape
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
}
