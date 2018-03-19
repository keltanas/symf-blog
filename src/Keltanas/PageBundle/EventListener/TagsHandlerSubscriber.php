<?php
/**
 * This file is part of the @package@.
 *
 * @author: Nikolay Ermin <keltanas@gmail.com>
 * @version: @version@
 */

namespace Keltanas\PageBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Keltanas\PageBundle\Entity\Post;
use Keltanas\PageBundle\Entity\Tag;
use Keltanas\PageBundle\Event\PostEvent;
use Keltanas\PageBundle\Repository\TagRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TagsHandlerSubscriber implements EventSubscriberInterface
{
    /** @var EntityManager */
    private $em;

    /**
     *
     * For instance:
     *
     * array('eventName' => 'methodName')
     * array('eventName' => array('methodName', $priority))
     * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            PostEvent::POST_CREATE => 'onPostCreate',
            PostEvent::POST_UPDATE => 'onPostUpdate',
            PostEvent::POST_REMOVE => 'onPostRemove',
        ];
    }

    public function onPostRemove(PostEvent $event)
    {
        $this->em = $event->getEntityManager();
        $this->removeTags($event->getEntity()->getTagsArray($event->getEntity()->getTags()));
    }

    public function onPostCreate(PostEvent $event)
    {
        $this->em = $event->getEntityManager();

        if (Post::STATUS_PUBLIC == $event->getEntity()->getStatus()) {
            $this->addTags($event->getEntity()->getTagsArray($event->getEntity()->getTags()));
        }
    }

    public function onPostUpdate(PostEvent $event)
    {
        $this->em = $event->getEntityManager();

        /** @var Post $entity */
        $entity = $event->getEntity();

        $uow = $this->em->getUnitOfWork();
        $uow->computeChangeSet($this->em->getClassMetadata(get_class($entity)), $entity);

        $changeSet = $uow->getEntityChangeSet($entity);
        if (!isset($changeSet['tags'])) {
            return;
        }
        if (isset($changeSet['status'])) {
            $oldStatus = $changeSet['status'][0];
            $newStatus = $changeSet['status'][1];
        } else {
            $oldStatus = $newStatus = $entity->getStatus();
        }

        $oldTags = $changeSet['tags'][0];
        $oldTagsArray = $entity->getTagsArray($oldTags);

        $newTags = $changeSet['tags'][1];
        $newTagsArray = $entity->getTagsArray($newTags);

        if ($newStatus && $oldStatus) {
            $this->addTags(array_values(array_diff($newTagsArray, $oldTagsArray)));
            $this->removeTags(array_values(array_diff($oldTagsArray, $newTagsArray)));
        } elseif ($newStatus && !$oldStatus) {
            $this->addTags(array_values($newTagsArray));
        } elseif (!$newStatus && $oldStatus) {
            $this->removeTags(array_values($oldTagsArray));
        }
    }

    /**
     * @param array $tags
     */
    private function addTags(array $tags)
    {
        if (!$tags) {
            return;
        }
        /** @var TagRepository $tagRepository */
        $tagRepository = $this->em->getRepository(Tag::class);
        foreach ($tags as $tagName) {
            $tag = $tagRepository->findOneBy(['name'=>$tagName]);
            if (!$tag) {
                $tag = new Tag();
                $tag->setName($tagName);
                $tag->setFreq(0);
            }
            $tag->setFreq($tag->getFreq() + 1);
            $this->em->persist($tag);
            $this->em->getUnitOfWork()->computeChangeSet($this->em->getClassMetadata(get_class($tag)), $tag);
        }
    }

    /**
     * @param array $tags
     */
    private function removeTags(array $tags)
    {
        if (!$tags) {
            return;
        }
        /** @var TagRepository $tagRepository */
        $tagRepository = $this->em->getRepository(Tag::class);
        $tagsList = $tagRepository->findBy(['name'=>$tags]);
        /** @var Tag $tag */
        foreach ($tagsList as $tag) {
            $tag->setFreq($tag->getFreq() - 1);
            $this->em->persist($tag);
            $this->em->getUnitOfWork()->computeChangeSet($this->em->getClassMetadata(get_class($tag)), $tag);
        }
    }
}
