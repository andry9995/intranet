<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MessageNouveau
 *
 * @ORM\Table(name="message_nouveau", indexes={@ORM\Index(name="fk_panier_id_mess_idx", columns={"id_panier"}), @ORM\Index(name="fk_operateurid_dest_idx", columns={"id_operateur_dest"})})
 * @ORM\Entity
 */
class MessageNouveau
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
     * @var \AppBundle\Entity\Panier
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Panier")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_panier", referencedColumnName="id")
     * })
     */
    private $idPanier;

    /**
     * @var \AppBundle\Entity\Operateur
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Operateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_operateur_dest", referencedColumnName="id")
     * })
     */
    private $idOperateurDest;



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
     * Set idPanier
     *
     * @param \AppBundle\Entity\Panier $idPanier
     *
     * @return MessageNouveau
     */
    public function setIdPanier(\AppBundle\Entity\Panier $idPanier = null)
    {
        $this->idPanier = $idPanier;

        return $this;
    }

    /**
     * Get idPanier
     *
     * @return \AppBundle\Entity\Panier
     */
    public function getIdPanier()
    {
        return $this->idPanier;
    }

    /**
     * Set idOperateurDest
     *
     * @param \AppBundle\Entity\Operateur $idOperateurDest
     *
     * @return MessageNouveau
     */
    public function setIdOperateurDest(\AppBundle\Entity\Operateur $idOperateurDest = null)
    {
        $this->idOperateurDest = $idOperateurDest;

        return $this;
    }

    /**
     * Get idOperateurDest
     *
     * @return \AppBundle\Entity\Operateur
     */
    public function getIdOperateurDest()
    {
        return $this->idOperateurDest;
    }
}
