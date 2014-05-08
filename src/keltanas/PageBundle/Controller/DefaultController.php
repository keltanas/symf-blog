<?php

namespace keltanas\PageBundle\Controller;

use keltanas\PageBundle\Repository\PostRepository;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $em = $this->getEntityManager();
        /** @var PostRepository $rep */
        $rep = $em->getRepository('keltanasPageBundle:Post');

        $paginator = $this->getKnpPaginator();
        $query = $rep->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', 1)
            ->orderBy('p.createdAt', 'desc')
            ->getQuery();
        $count = $rep->getCount();

        $query->setHint('knp_paginator.count', $count);

        /** @var $pagination SlidingPagination */
        $entities = $paginator->paginate(
            $query,
            $this->getRequest()->query->getInt('page', 1),
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





    /**
     * @return Paginator
     */
    private function getKnpPaginator()
    {
        return $this->get('knp_paginator');
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    private function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }
}
