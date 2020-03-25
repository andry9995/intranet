<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

 /**
 * Doublon
 *
 * @ORM\Table(name="doublon")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DoublonRepository")
 */

class Doublon
{
	/**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
 }

