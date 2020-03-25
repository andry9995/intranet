<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DecoupageParam
 *
 * @ORM\Table(name="decoupage_param")
 * @ORM\Entity
 */
class DecoupageParam
{
    /**
     * @var string
     *
     * @ORM\Column(name="dir_temp_decoupe_piece", type="string", length=100, nullable=false)
     */
    private $dirTempDecoupePiece;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set dirTempDecoupePiece
     *
     * @param string $dirTempDecoupePiece
     *
     * @return DecoupageParam
     */
    public function setDirTempDecoupePiece($dirTempDecoupePiece)
    {
        $this->dirTempDecoupePiece = $dirTempDecoupePiece;

        return $this;
    }

    /**
     * Get dirTempDecoupePiece
     *
     * @return string
     */
    public function getDirTempDecoupePiece()
    {
        return $this->dirTempDecoupePiece;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return DecoupageParam
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
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
}
