<?php

namespace keltanas\PageBundle\Controller;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\OptimisticLockException;
use keltanas\Common\Controller;
use keltanas\PageBundle\Event\PostEvent;
use keltanas\PageBundle\Repository\PostRepository;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Symfony\Component\HttpFoundation\Request;

use keltanas\PageBundle\Entity\Post;
use keltanas\PageBundle\Form\PostType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Post controller.
 */
class PostController extends Controller
{
    /**
     * Lists all Post entities.
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var PostRepository $repository */
        $repository = $em->getRepository('keltanasPageBundle:Post');

        $paginator = $this->getKnpPaginator();

        $user = null;
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
        }

        $query = $repository->getQueryForPaginator(null, $user);

        /** @var $pagination SlidingPagination */
        $entities = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
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
            $em->persist($entity);
            $entity->setAccount($this->getUser());
            $event = new PostEvent($em, $entity);
            $this->getEventDispatcher()->dispatch(PostEvent::POST_CREATE, $event);
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

        $editForm = $this->createForm(new PostType(), $entity);
        $editForm->submit($request);

        try {
            if ($editForm->isValid()) {
                $em->lock($entity, LockMode::OPTIMISTIC, $entity->getVersion());
                $em->persist($entity);
                $event = new PostEvent($em, $entity);
                $this->getEventDispatcher()->dispatch(PostEvent::POST_UPDATE, $event);
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
            $event = new PostEvent($em, $entity);
            $this->getEventDispatcher()->dispatch(PostEvent::POST_REMOVE, $event);
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

}
