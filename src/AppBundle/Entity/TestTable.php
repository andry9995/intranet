<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TestTable
 *
 * @ORM\Table(name="test_table")
 * @ORM\Entity
 */
class TestTable
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="test_tablecol", type="date", nullable=true)
     */
    private $testTablecol;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set testTablecol
     *
     * @param \DateTime $testTablecol
     *
     * @return TestTable
     */
    public function setTestTablecol($testTablecol)
    {
        $this->testTablecol = $testTablecol;

        return $this;
    }

    /**
     * Get testTablecol
     *
     * @return \DateTime
     */
    public function getTestTablecol()
    {
        return $this->testTablecol;
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
