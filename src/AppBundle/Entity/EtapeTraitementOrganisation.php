<?php

namespace AppBundle\Entity;

/**
 * EtapeTraitementOrganisation
 */
class EtapeTraitementOrganisation
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Organisation
     */
    private $organisation;

    /**
     * @var \AppBundle\Entity\EtapeTraitement
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
     * Set organisation
     *
     * @param \AppBundle\Entity\Organisation $organisation
     *
     * @return EtapeTraitementOrganisation
     */
    public function setOrganisation(\AppBundle\Entity\Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get organisation
     *
     * @return \AppBundle\Entity\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set etapeTraitement
     *
     * @param \AppBundle\Entity\EtapeTraitement $etapeTraitement
     *
     * @return EtapeTraitementOrganisation
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

