<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Annotations\AnnotationRegistry;
AnnotationRegistry::registerLoader('class_exists');

/**
 * @ORM\Entity
 * @ORM\Table(name="GalleryItems")
 * @ORM\Entity(repositoryClass="App\Repository\GalleryItemsRepository")
 * 
 */
class Blog
{
     /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;

      /**
     * @ORM\Column(name="directory", type="string", length=65535)
     * @var string
     */

    private $directory;

      /**
     * @ORM\Column(name="fileName", type="string", length=250)
     * @var string
     */

    private $fileName;

    /**
     * Many items have one gallery. This is the owning side.
     * @ORM\ManyToOne(targetEntity="Blog2", fetch="EAGER", inversedBy="items", cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
     * 
     */

    private $gallery;
 
    public function getGallery()
    {

        return $this->gallery;

    }

    public function setGallery($gallery)
    {

        $this->gallery = $gallery;

    }

    public function setDirectory(string $directory) { return $this->directory = $directory; }
    public function setFileName(string $fileName) { return $this->fileName = $fileName; }

    public function getDirectory() { return $this->directory; }
    public function getFileName() { return $this->fileName; }
    public function getId() { return $this->id; }

}
