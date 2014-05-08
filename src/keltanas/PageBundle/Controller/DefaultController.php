<?php

namespace keltanas\PageBundle\Controller;

use keltanas\Common\Controller;
use keltanas\PageBundle\Repository\PostRepository;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Template()
     *
     * @param Request $request
     *
     * @return array
     */
    public function indexAction(Request $request)
    {
        $em = $this->getEntityManager();
        /** @var PostRepository $repository */
        $repository = $em->getRepository('keltanasPageBundle:Post');

        $paginator = $this->getKnpPaginator();

        /** @var $pagination SlidingPagination */
        $entities = $paginator->paginate(
            $repository->getQueryForPaginator(),
            $request->query->getInt('page', 1),
            10
        );

        return [
            'entities' => $entities,
        ];
    }

    /**
     * @Template()
     *
     * @return array
     */
    public function aboutAction()
    {
        return [];
    }

    /**
     * @Template()
     *
     * @return array
     */
    public function portfolioAction()
    {
        return [];
    }
}
