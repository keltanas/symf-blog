<?php
/**
 *
 * @author: Nikolay Ermin <keltanas@gmail.com>
 */

namespace Keltanas\PageBundle\Controller;

use Keltanas\Common\Controller;
use Keltanas\PageBundle\Repository\PostRepository;
use Keltanas\PageBundle\Repository\TagRepository;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Component\HttpFoundation\Request;

class TagController extends Controller
{
    /**
     * @param Request $request
     * @param $name
     *
     * @return array
     */
    public function indexAction(Request $request, $name)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var PostRepository $repository */
        $repository = $em->getRepository('KeltanasPageBundle:Post');

        $paginator = $this->getKnpPaginator();

        /** @var $pagination SlidingPagination */
        $entities = $paginator->paginate(
            $repository->getQueryForPaginator($name),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('KeltanasPageBundle:Tag:index.html.twig', [
            'entities' => $entities,
            'name' => $name,
        ]);
    }

    public function tagsCloudAction($max = 20)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var TagRepository $repository */
        $repository = $em->getRepository('KeltanasPageBundle:Tag');

        $qb = $repository->createQueryBuilder('t')->where('t.freq > 0')->orderBy('t.freq', 'desc')->setMaxResults($max);
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
            'KeltanasPageBundle:Tag:tagsCloud.html.twig', [
                'maxFreq' => $maxFreq,
                'tags' => $tags,
            ]
        );
    }

}
