<?php

namespace keltanas\PageBundle\Controller;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\OptimisticLockException;
use keltanas\PageBundle\Entity\Tag;
use keltanas\PageBundle\Repository\PostRepository;
use keltanas\PageBundle\Repository\TagRepository;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use keltanas\PageBundle\Entity\Post;
use keltanas\PageBundle\Form\PostType;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Post controller.
 *
 */
class PostController extends Controller
{

    /**
     * Lists all Post entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        /** @var PostRepository $rep */
        $rep = $em->getRepository('keltanasPageBundle:Post');

        $paginator = $this->getKnpPaginator();
        $qb = $rep->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'desc');

        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $qb->where('p.account = :user')->setParameter('user', $this->getUser()->getId());
        }

        $query = $qb->getQuery();
        $count = $rep->getCount($this->getUser());

        $query->setHint('knp_paginator.count', $count);

        /** @var $pagination SlidingPagination */
        $entities = $paginator->paginate(
            $query,
            $this->getRequest()->query->getInt('page', 1),
            20
        );

        return $this->render('keltanasPageBundle:Post:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Creates a new Post entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Post();
        $form = $this->createForm(new PostType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setAccount($this->getUser());
            $entity->setContentCutedHtml(
                $this->getMarkdown()->transformMarkdown(explode('[cut]', $entity->getContentMd())[0])
            );
            $entity->setContentHtml(
                $this->getMarkdown()->transformMarkdown(str_replace('[cut]', '', $entity->getContentMd()))
            );
            if ($entity->getStatus()) {
                $this->addTags($entity->getTagsArray());
            }
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('post_show', array('id' => $entity->getId())));
        }

        return $this->render('keltanasPageBundle:Post:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Post entity.
     *
     */
    public function newAction()
    {
        $entity = new Post();
        $entity->setStatus(1);
        $form   = $this->createForm(new PostType(), $entity);

        return $this->render('keltanasPageBundle:Post:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Post entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('keltanasPageBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        return $this->render('keltanasPageBundle:Post:show.html.twig', array(
            'entity'      => $entity,
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Post $entity */
        $entity = $em->getRepository('keltanasPageBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            if ($entity->getAccount()->getId() !== $this->getUser()->getId()) {
                throw new AccessDeniedException("Post belongs to another author");
            }
        }

        $editForm = $this->createForm(new PostType(), $entity);

        return $this->render('keltanasPageBundle:Post:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }

    /**
     * Edits an existing Post entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Post $entity */
        $entity = $em->getRepository('keltanasPageBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            if ($entity->getAccount()->getId() !== $this->getUser()->getId()) {
                throw new AccessDeniedException("Post belongs to another author");
            }
        }

        $oldTags = $entity->getTagsArray();
        $oldStatus = $entity->getStatus();
        $editForm = $this->createForm(new PostType(), $entity);
        $editForm->submit($request);

        try {
            if ($editForm->isValid()) {
                $em->lock($entity, LockMode::OPTIMISTIC, $entity->getVersion());
                $entity->setContentCutedHtml(
                    $this->getMarkdown()->transformMarkdown(explode('[cut]', $entity->getContentMd())[0])
                );
                $entity->setContentHtml(
                    $this->getMarkdown()->transformMarkdown(str_replace('[cut]', '', $entity->getContentMd()))
                );

                $newTags = $entity->getTagsArray();
                $newStatus = $entity->getStatus();
                if ($newStatus && $newStatus == $oldStatus) {
                    $this->addTags(array_values(array_diff($newTags, $oldTags)));
                    $this->removeTags(array_values(array_diff($oldTags, $newTags)));
                } elseif ($newStatus && !$oldStatus) {
                    $this->addTags(array_values($newTags));
                } elseif (!$newStatus && $oldStatus) {
                    $this->removeTags(array_values($oldTags));
                }

                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('post_show', array('id' => $id)));
            }
        } catch(OptimisticLockException $e) {
            $this->getFlashBag()
                ->add('error', "Sorry, but someone else has already changed this entity. Please apply the changes again!");
        }

        return $this->render('keltanasPageBundle:Post:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }
    /**
     * Deletes a Post entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->submit($request);

        $em = $this->getDoctrine()->getManager();

        /** @var Post $entity */
        $entity = $em->getRepository('keltanasPageBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            if ($entity->getAccount()->getId() !== $this->getUser()->getId()) {
                throw new AccessDeniedException("Post belongs to another author");
            }
        }

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('keltanasPageBundle:Post')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Post entity.');
            }

            $em->remove($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('post'));
        }

        return $this->render('keltanasPageBundle:Post:delete.html.twig', array(
            'entity' => $entity,
            'delete_form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to delete a Post entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }


    /**
     * @return \Knp\Bundle\MarkdownBundle\Parser\MarkdownParser
     */
    public function getMarkdown()
    {
        return $this->get('markdown.parser');
    }

    /**
     * @return FlashBag
     */
    public function getFlashBag()
    {
        return $this->get('session')->getFlashBag();
    }

    /**
     * @return Paginator
     */
    public function getKnpPaginator()
    {
        return $this->get('knp_paginator');
    }

    /**
     * @param array $tags
     */
    protected function addTags(array $tags)
    {
        if (!$tags) {
            return;
        }
        $em = $this->getDoctrine()->getManager();
        /** @var TagRepository $tagRepository */
        $tagRepository = $em->getRepository('keltanasPageBundle:Tag');
        foreach ($tags as $tagName) {
            $tag = $tagRepository->findOneBy(['name'=>$tagName]);
            if (!$tag) {
                $tag = new Tag();
                $tag->setName($tagName);
                $tag->setFreq(0);
            }
            $tag->setFreq($tag->getFreq() + 1);
            $em->persist($tag);
        }
    }

    /**
     * @param array $tags
     */
    protected function removeTags(array $tags)
    {
        if (!$tags) {
            return;
        }
        $em = $this->getDoctrine()->getManager();
        /** @var TagRepository $tagRepository */
        $tagRepository = $em->getRepository('keltanasPageBundle:Tag');
        $tagsList = $tagRepository->findBy(['name'=>$tags]);
        /** @var Tag $tag */
        foreach ($tagsList as $tag) {
            $tag->setFreq($tag->getFreq() - 1);
            $em->persist($tag);
        }
    }
}
