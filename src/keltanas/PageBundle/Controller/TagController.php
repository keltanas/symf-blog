<?php
/**
 *
 * @author: Nikolay Ermin <keltanas@gmail.com>
 */

namespace keltanas\PageBundle\Controller;


use keltanas\PageBundle\Entity\Tag;
use keltanas\PageBundle\Repository\PostRepository;
use keltanas\PageBundle\Repository\TagRepository;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class TagController extends Controller
{
    /**
     * @param $name
     *
     * @Template()
     *
     * @return array
     */
    public function indexAction($name)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var PostRepository $rep */
        $rep = $em->getRepository('keltanasPageBundle:Post');

        $paginator = $this->getKnpPaginator();

        $qb = $rep->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', 1)
            ->andWhere('p.tags LIKE :name')
            ->setParameter('name', '%'.$name.'%')
            ->orderBy('p.createdAt', 'desc')
            ;

        $query = $qb->getQuery();

        $qb->select($qb->expr()->count('p'));
        $count = $qb->getQuery()->getSingleScalarResult();
        $query->setHint('knp_paginator.count', $count);

        /** @var $pagination SlidingPagination */
        $entities = $paginator->paginate(
            $query,
            $this->getRequest()->query->getInt('page', 1),
            10
        );

        return [
            'entities' => $entities,
            'name' => $name,
        ];
    }

    public function tagsCloudAction($max = 20)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var TagRepository $rep */
        $rep = $em->getRepository('keltanasPageBundle:Tag');

        $qb = $rep->createQueryBuilder('t')->where('t.freq > 0')->orderBy('t.freq', 'desc')->setMaxResults($max);
        $tags = $qb->getQuery()->getArrayResult();

        $maxFreq = array_reduce($tags, function($max, $tag){
                return $max = ($tag['freq'] > $max ? $tag['freq'] : $max);
            }, 0);

        $tags = array_map(function($tag) use ($maxFreq) {
                $tag['freqPercent'] = round(15 * $tag['freq'] / $maxFreq) + 10;
                return $tag;
            }, $tags);

        usort($tags, function($tag1, $tag2) {
                return strcasecmp($tag1['name'], $tag2['name']);
            });

        return $this->render(
            'keltanasPageBundle:Tag:tagsCloud.html.twig', [
                'maxFreq' => $maxFreq,
                'tags' => $tags,
            ]
        );
    }

    /**
     * @return Paginator
     */
    public function getKnpPaginator()
    {
        return $this->get('knp_paginator');
    }
}
