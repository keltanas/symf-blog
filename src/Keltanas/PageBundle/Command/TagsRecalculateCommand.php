<?php

namespace Keltanas\PageBundle\Command;

use Doctrine\ORM\UnexpectedResultException;
use Keltanas\PageBundle\Entity\Post;
use Keltanas\PageBundle\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function var_dump;

/**
 * Class TagsRecalculateCommand
 * @package Keltanas\PageBundle\Command
 */
class TagsRecalculateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('page:tags:recalculate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $postRepository = $doctrine->getRepository(Post::class);
        $tagsRepository = $doctrine->getRepository(Tag::class);

        try {
            $count = $postRepository->getCount();
        } catch (UnexpectedResultException $e) {
            $output->writeln('<error>Подходящих постов не найдено.</error>');
            return;
        }

        $output->writeln("<info>Найдено {$count} постов.</info>");

        $start = 0;
        $limit = 100;
        $tags = [];

        while ($start <= $count) {
            /** @var Post $post */
            foreach ($postRepository->getAll($start, $limit) as $post) {
                $postTags = $post->getTagsArray($post->getTags());
                foreach ($postTags as $tag) {
                    if (isset($tags[$tag])) {
                        $tags[$tag]++;
                    } else {
                        $tags[$tag] = 1;
                    }
                }
            }

            $start = $start + $limit;
        }

        /** @var Tag $tag */
        foreach ($tagsRepository->findAll() as $tag) {
            if (isset($tags[$tag->getName()])) {
                $tag->setFreq($tags[$tag->getName()]);
                unset($tags[$tag->getName()]);
                $doctrine->getManager()->persist($tag);
                $output->writeln("<comment>Обновлен тег {$tag->getName()}: {$tag->getFreq()}</comment>");
            }
        }

        foreach ($tags as $tagName => $freq) {
            $tag = new Tag();
            $tag->setName($tagName);
            $tag->setFreq($freq);
            $doctrine->getManager()->persist($tag);
            $output->writeln("<comment>Добавлен тег {$tag->getName()}: {$tag->getFreq()}</comment>");
        }
        $doctrine->getManager()->flush();
    }
}
