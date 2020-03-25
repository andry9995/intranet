<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CarteBleuBanqueCompte
 *
 * @ORM\Table(name="carte_bleu_banque_compte", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQUE", columns={"num_cb", "banque_compte_id"})}, indexes={@ORM\Index(name="fk_carte_bleu_banque_compte_idx", columns={"banque_compte_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CarteBleuBanqueCompteRepository")
 */
class CarteBleuBanqueCompte
{
    /**
     * @var string
     *
     * @ORM\Column(name="num_cb", type="string", length=45, nullable=false)
     */
    private $numCb;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\BanqueCompte
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\BanqueCompte")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="banque_compte_id", referencedColumnName="id")
     * })
     */
    private $banqueCompte;

    /**
     * @var
     *
     * @ORM\Column(name="type_recherche", type="integer", nullable= true)
     */
    private $typeRecherche;

    /**
     * @var
     *
     * @ORM\Column(name="type_cb", type="integer", nullable= true)
     */
    private $typeCb;



    /**
     * Set numCb
     *
     * @param string $numCb
     *
     * @return CarteBleuBanqueCompte
     */
    public function setNumCb($numCb)
    {
        $this->numCb = $numCb;

        return $this;
    }

    /**
     * Get numCb
     *
     * @return string
     */
    public function getNumCb()
    {
        return $this->numCb;
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
     * Set banqueCompte
     *
     * @param \AppBundle\Entity\BanqueCompte $banqueCompte
     *
     * @return CarteBleuBanqueCompte
     */
    public function setBanqueCompte(\AppBundle\Entity\BanqueCompte $banqueCompte = null)
    {
        $this->banqueCompte = $banqueCompte;

        return $this;
    }

    /**
     * Get banqueCompte
     *
     * @return \AppBundle\Entity\BanqueCompte
     */
    public function getBanqueCompte()
    {
        return $this->banqueCompte;
    }

    /**
     * @param $typeRecherche
     * @return $this
     */
    public function setTypeRecherche($typeRecherche){
        $this->typeRecherche = $typeRecherche;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTypeRecherche(){
        return $this->typeRecherche;
    }

    /**
     * @param $typeCb
     * @return $this
     */
    public function setTypeCb($typeCb){
        $this->typeCb = $typeCb;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTypeCb(){
        return $this->typeCb;
    }
}
