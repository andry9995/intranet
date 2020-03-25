<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OneArticle
 *
 * @ORM\Table(name="one_article", indexes={@ORM\Index(name="fk_one_article_one_unite_article1_idx", columns={"one_unite_article_id"}), @ORM\Index(name="fk_one_article_one_famille_article1_idx", columns={"one_famille_article_id"})})
 * @ORM\Entity
 */
class OneArticle
{
    /**
     * @var float
     *
     * @ORM\Column(name="prix_achat", type="float", precision=10, scale=0, nullable=true)
     */
    private $prixAchat = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="prix_vente", type="float", precision=10, scale=0, nullable=true)
     */
    private $prixVente = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=50, nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\OneUniteArticle
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OneUniteArticle")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="one_unite_article_id", referencedColumnName="id")
     * })
     */
    private $oneUniteArticle;

    /**
     * @var \AppBundle\Entity\OneFamilleArticle
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OneFamilleArticle")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="one_famille_article_id", referencedColumnName="id")
     * })
     */
    private $oneFamilleArticle;



    /**
     * Set prixAchat
     *
     * @param float $prixAchat
     *
     * @return OneArticle
     */
    public function setPrixAchat($prixAchat)
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }

    /**
     * Get prixAchat
     *
     * @return float
     */
    public function getPrixAchat()
    {
        return $this->prixAchat;
    }

    /**
     * Set prixVente
     *
     * @param float $prixVente
     *
     * @return OneArticle
     */
    public function setPrixVente($prixVente)
    {
        $this->prixVente = $prixVente;

        return $this;
    }

    /**
     * Get prixVente
     *
     * @return float
     */
    public function getPrixVente()
    {
        return $this->prixVente;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return OneArticle
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return OneArticle
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * Set oneUniteArticle
     *
     * @param \AppBundle\Entity\OneUniteArticle $oneUniteArticle
     *
     * @return OneArticle
     */
    public function setOneUniteArticle(\AppBundle\Entity\OneUniteArticle $oneUniteArticle = null)
    {
        $this->oneUniteArticle = $oneUniteArticle;

        return $this;
    }

    /**
     * Get oneUniteArticle
     *
     * @return \AppBundle\Entity\OneUniteArticle
     */
    public function getOneUniteArticle()
    {
        return $this->oneUniteArticle;
    }

    /**
     * Set oneFamilleArticle
     *
     * @param \AppBundle\Entity\OneFamilleArticle $oneFamilleArticle
     *
     * @return OneArticle
     */
    public function setOneFamilleArticle(\AppBundle\Entity\OneFamilleArticle $oneFamilleArticle = null)
    {
        $this->oneFamilleArticle = $oneFamilleArticle;

        return $this;
    }

    /**
     * Get oneFamilleArticle
     *
     * @return \AppBundle\Entity\OneFamilleArticle
     */
    public function getOneFamilleArticle()
    {
        return $this->oneFamilleArticle;
    }
}
