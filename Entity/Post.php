<?php

namespace Isometriks\Bundle\SymEditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Isometriks\Bundle\SymEditBundle\Util\Util;
use Isometriks\Bundle\SymEditBundle\Model\UpdatableInterface; 

/**
 * Isometriks\Bundle\SymEditBundle\Entity\Post
 *
 * @ORM\Table(name="post")
 * @ORM\Entity(repositoryClass="Isometriks\Bundle\SymEditBundle\Repository\PostRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"slug"}, message="The Post slug must be unique. It is created from your post title.")
 */
class Post implements UpdatableInterface
{

    const DRAFT = 0; 
    const PUBLISHED = 1; 
    
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="Image", cascade={"persist"})
     */
    private $image;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @var string $content
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;
    
    /**
     * @ORM\Column(name="summary", type="text", nullable=true)
     */
    private $summary; 
    
    /**
     * @ORM\Column(name="createdAt", type="datetime")
     */    
    private $createdAt; 
    
    /**
     * @ORM\Column(name="updatedAt", type="datetime")
     */
    private $updatedAt; 
    
    /**
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="posts")
     */
    private $categories; 
    
    /**
     * @ORM\Column(name="status", type="integer")
     */
    private $status; 
    
    /**
     * @var array $seo
     *
     * @ORM\Column(name="seo", type="json_array", nullable=true)
     */
    private $seo;

    
    public function __construct()
    {
        $this->setCreatedAt(new \DateTime()); 
        $this->setUpdatedAt(new \DateTime()); 
        $this->setStatus(self::DRAFT); 
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
     * Set title
     *
     * @param string $title
     * @return Post
     */
    public function setTitle($title)
    {
        $this->title = $title;
        $this->createSlug();

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Post
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Post
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set author
     *
     * @param Isometriks\Bundle\SymEditBundle\Entity\User $author
     * @return Post
     */
    public function setAuthor(\Isometriks\Bundle\SymEditBundle\Entity\User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return Isometriks\Bundle\SymEditBundle\Entity\User 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set image
     *
     * @param Isometriks\Bundle\SymEditBundle\Entity\Image $image
     * @return Post
     */
    public function setImage(\Isometriks\Bundle\SymEditBundle\Entity\Image $image = null)
    {
        $this->image = $image; 
        $this->setUpdated(); 
        
        return $this;
    }

    /**
     * Get image
     *
     * @return Isometriks\Bundle\SymEditBundle\Entity\Image 
     */
    public function getImage()
    {
        return $this->image;
    }

    private function createSlug()
    {
        if ($this->getSlug() === null) {
            $slug = Util::slugify($this->getTitle(), 50);
            $this->setSlug($slug);
        }
        
        return $this->getSlug(); 
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Post
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }


    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Post
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdated()
    {
        $this->setUpdatedAt(new \DateTime()); 
        
        if($image = $this->getImage()){
            if($image->hasFile()){
                $image->setName($this->createSlug()); 
            }
        }
    }

    /**
     * Add categories
     *
     * @param Isometriks\Bundle\SymEditBundle\Entity\Category $categories
     * @return Post
     */
    public function addCategory(\Isometriks\Bundle\SymEditBundle\Entity\Category $categories)
    {
        $this->categories[] = $categories;
    
        return $this;
    }

    /**
     * Remove categories
     *
     * @param Isometriks\Bundle\SymEditBundle\Entity\Category $categories
     */
    public function removeCategory(\Isometriks\Bundle\SymEditBundle\Entity\Category $categories)
    {
        $this->categories->removeElement($categories);
    }

    /**
     * Get categories
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set seo
     *
     * @param array $seo
     * @return Post
     */
    public function setSeo(array $seo = array())
    {
        $this->seo = $seo;
    
        return $this;
    }

    /**
     * Get seo
     *
     * @return array 
     */
    public function getSeo()
    {
        return $this->seo;
    }

    /**
     * Set summary
     *
     * @param string $summary
     * @return Post
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    
        return $this;
    }

    /**
     * Get summary
     *
     * @return string 
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Post
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
}