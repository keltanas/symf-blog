<?php

namespace keltanas\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use keltanas\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Post
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="keltanas\PageBundle\Repository\PostRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Post extends ContainerAware
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="canonical_title", type="string", length=255)
     */
    private $canonicalTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="content_md", type="text")
     * @Assert\NotBlank()
     */
    private $contentMd;

    /**
     * @var string
     *
     * @ORM\Column(name="content_html", type="text")
     */
    private $contentHtml;

    /**
     * @var string
     *
     * @ORM\Column(name="content_cuted_html", type="text")
     */
    private $contentCutedHtml;

    /**
     * @var string
     *
     * @ORM\Column(name="tags", type="text", nullable=true)
     */
    private $tags;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var integer
     *
     * @ORM\Version @ORM\Column(name="version", type="integer")
     */
    private $version;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\keltanas\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     */
    private $account;


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
     * @param string $canonicalTitle
     */
    public function setCanonicalTitle($canonicalTitle)
    {
        $this->canonicalTitle = $canonicalTitle;
    }

    /**
     * @return string
     */
    public function getCanonicalTitle()
    {
        return $this->canonicalTitle;
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function setCaconicatTitleValue()
    {
        $this->canonicalTitle = "";
    }

    /**
     * Set contentMd
     *
     * @param string $contentMd
     * @return Post
     */
    public function setContentMd($contentMd)
    {
        $this->contentMd = $contentMd;

        return $this;
    }

    /**
     * Get contentMd
     *
     * @return string
     */
    public function getContentMd()
    {
        return $this->contentMd;
    }

    /**
     * @param string $contentCutedHtml
     */
    public function setContentCutedHtml($contentCutedHtml)
    {
        $this->contentCutedHtml = $contentCutedHtml;
    }

    /**
     * @return string
     */
    public function getContentCutedHtml()
    {
        return $this->contentCutedHtml ?: $this->getContentHtml();
    }

    /**
     * Set contentHtml
     *
     * @param string $contentHtml
     * @return Post
     */
    public function setContentHtml($contentHtml)
    {
        $this->contentHtml = $contentHtml;

        return $this;
    }

    /**
     * Get contentHtml
     *
     * @return string
     */
    public function getContentHtml()
    {
        return $this->contentHtml;
    }

    /**
     * Set tags
     *
     * @param string $tags
     * @return Post
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get tags
     *
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return array
     */
    public function getTagsArray()
    {
        return preg_split('/\s*,\s*/',trim($this->getTags()),-1,PREG_SPLIT_NO_EMPTY);
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
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime('now');
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
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTime('now');
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

    /**
     * Set version
     *
     * @param integer $version
     * @return Post
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param \keltanas\UserBundle\Entity\User $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * @return \keltanas\UserBundle\Entity\User
     */
    public function getAccount()
    {
        return $this->account;
    }


}
