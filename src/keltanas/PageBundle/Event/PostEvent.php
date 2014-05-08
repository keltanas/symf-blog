<?php
/**
 * This file is part of the @package@.
 *
 * @author: Nikolay Ermin <keltanas@gmail.com>
 * @version: @version@
 */

namespace keltanas\PageBundle\Event;

use Doctrine\ORM\EntityManager;
use keltanas\PageBundle\Entity\Post;
use Symfony\Component\EventDispatcher\Event;

class PostEvent extends Event
{
    const POST_CREATE = 'entity.post.create';
    const POST_UPDATE = 'entity.post.update';
    const POST_REMOVE = 'entity.post.remove';

    /** @var Post */
    private $entity;

    /** @var EntityManager */
    private $em;

    function __construct(EntityManager $em, Post $entity)
    {
        $this->em     = $em;
        $this->entity = $entity;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @return \keltanas\PageBundle\Entity\Post
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
