<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Annotations\AnnotationRegistry;
AnnotationRegistry::registerLoader('class_exists');

/**
 * @ORM\Entity
 * @ORM\Table(name="Gallery")
 * @ORM\Entity(repositoryClass="App\Repository\GalleryRepository")
 * 
 */
class Blog2
{
     /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string", length=250)
     * @var string
     */
    private $title;

     /**
     * @ORM\Column(name="description", type="text")
     * @var string
     */

    private $description;

     /**
     * @ORM\Column(name="date", type="string", length=250)
     * @var string
     */

    private $date;

      /**
     * @ORM\Column(name="type", type="string", length=250)
     * @var string
     */

    private $type;
 
    /**
     * One product has many features. This is the inverse side.
     * @ORM\OneToMany(targetEntity="Blog", mappedBy="gallery", cascade={"remove", "persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="gallery_id")
     * 
     */

    private $items;

    public function __construct()
    {

        $this->items = new ArrayCollection();

    }

    public function getItems(): Collection
    {

        return $this->items;

    }

    public function setTitle(string $title) { return $this->title = $title; }
    public function setDescription(string $description) { return $this->description = $description; }
    public function setDate(string $date) { return $this->date = $date; }
    public function setType(string $type) { return $this->type = $type; }
    

    public function getTitle() { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getDate() { return $this->date; }
    public function getType() { return $this->type; }
    public function getId() { return $this->id; }

}
