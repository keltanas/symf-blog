<?php
/**
 * This file is part of the @package@.
 *
 * @author: Nikolay Ermin <keltanas@gmail.com>
 * @version: @version@
 */

namespace keltanas\PageBundle\EventListener;

use Doctrine\ORM\EntityManager;
use keltanas\PageBundle\Entity\Post;
use keltanas\PageBundle\Entity\Tag;
use keltanas\PageBundle\Event\PostEvent;
use keltanas\PageBundle\Repository\TagRepository;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TagsHandlerSubscriber implements EventSubscriberInterface
{
    /** @var  MarkdownParserInterface */
    private $markdown;

    /** @var EntityManager */
    private $em;

    /**
     * @param \Knp\Bundle\MarkdownBundle\MarkdownParserInterface $markdown
     */
    public function setMarkdown($markdown)
    {
        $this->markdown = $markdown;
    }

    /**
     * @return \Knp\Bundle\MarkdownBundle\MarkdownParserInterface
     */
    public function getMarkdown()
    {
        return $this->markdown;
    }

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
        $this->removeTags($event->getEntity()->getTagsArray());
    }

    public function onPostCreate(PostEvent $event)
    {
        $this->em = $event->getEntityManager();
        $this->handleMarkdown($event->getEntity());

        if (Post::STATUS_PUBLIC == $event->getEntity()->getStatus()) {
            $this->addTags($event->getEntity()->getTagsArray());
        }
    }

    public function onPostUpdate(PostEvent $event)
    {
        $this->em = $event->getEntityManager();

        /** @var Post $entity */
        $entity = $event->getEntity();

        $this->handleMarkdown($entity);

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

        if ($newStatus && $newStatus == $oldStatus) {
            $this->addTags(array_values(array_diff($newTagsArray, $oldTagsArray)));
            $this->removeTags(array_values(array_diff($oldTagsArray, $newTagsArray)));
        } elseif ($newStatus && !$oldStatus) {
            $this->addTags(array_values($newTagsArray));
        } elseif (!$newStatus && $oldStatus) {
            $this->removeTags(array_values($oldTagsArray));
        }
    }

    private function handleMarkdown(Post $entity)
    {
        $entity->setContentCutedHtml(
            $this->getMarkdown()->transformMarkdown(explode('[cut]', $entity->getContentMd())[0])
        );
        $entity->setContentHtml(
            $this->getMarkdown()->transformMarkdown(str_replace('[cut]', '', $entity->getContentMd()))
        );
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
        $tagRepository = $this->em->getRepository('keltanasPageBundle:Tag');
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
        $tagRepository = $this->em->getRepository('keltanasPageBundle:Tag');
        $tagsList = $tagRepository->findBy(['name'=>$tags]);
        /** @var Tag $tag */
        foreach ($tagsList as $tag) {
            $tag->setFreq($tag->getFreq() - 1);
            $this->em->persist($tag);
            $this->em->getUnitOfWork()->computeChangeSet($this->em->getClassMetadata(get_class($tag)), $tag);
        }
    }
}
