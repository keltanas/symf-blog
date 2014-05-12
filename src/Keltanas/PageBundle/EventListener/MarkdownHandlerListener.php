<?php
/**
 * This file is part of the @package@.
 *
 * @author: Nikolay Ermin <keltanas@gmail.com>
 * @version: @version@
 */

namespace Keltanas\PageBundle\EventListener;


use Keltanas\PageBundle\Entity\Post;
use Keltanas\PageBundle\Event\PostEvent;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;

class MarkdownHandlerListener
{
    /** @var  MarkdownParserInterface */
    private $markdown;

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

    public function onPostModify(PostEvent $event)
    {
        $this->handleMarkdown($event->getEntity());
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
}
