<?php

namespace Keltanas\PageBundle\Controller;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\OptimisticLockException;
use Keltanas\Common\Controller;
use Keltanas\PageBundle\Event\PostEvent;
use Keltanas\PageBundle\Repository\PostRepository;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Symfony\Component\HttpFoundation\Request;

use Keltanas\PageBundle\Entity\Post;
use Keltanas\PageBundle\Form\PostType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Post controller.
 */
class PostController extends Controller
{
    /**
     * Lists all Post entities.
     *
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var PostRepository $repository */
        $repository = $em->getRepository('KeltanasPageBundle:Post');

        $paginator = $this->getKnpPaginator();

        $user = $this->isGranted('ROLE_ADMIN') ? null : $this->getUser();

        $query = $repository->getQueryForPaginator(null, $user);

        /** @var $pagination SlidingPagination */
        $entities = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        return [
            'entities' => $entities,
        ];
    }

    /**
     * Creates a new Post entity.
     *
     * @Template("KeltanasPageBundle:Post:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Post();
        $form = $this->createForm(new PostType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $this->getEntityManager()->persist($entity);
            $entity->setAccount($this->getUser());
            $this->getEventDispatcher()->dispatch(PostEvent::POST_CREATE, new PostEvent($this->getEntityManager(), $entity));
            $this->getEntityManager()->flush();

            return $this->redirectToPath('post_show', ['id' => $entity->getId()]);
        }

        return ['entity' => $entity, 'form' => $form->createView()];
    }

    /**
     * Displays a form to create a new Post entity.
     *
     * @Template()
     */
    public function newAction()
    {
        $entity = new Post();
        $entity->setStatus(1);
        $form   = $this->createForm(new PostType(), $entity);

        return [
            'entity' => $entity,
            'form'   => $form->createView(),
        ];
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('KeltanasPageBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        return [
            'entity'      => $entity,
        ];
    }

    /**
     * Displays a form to edit an existing Post entity.
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Post $entity */
        $entity = $em->getRepository('KeltanasPageBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            if ($entity->getAccount()->getId() !== $this->getUser()->getId()) {
                throw new AccessDeniedException("Post belongs to another author");
            }
        }

        $editForm = $this->createForm(new PostType(), $entity);

        return [
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ];
    }

    /**
     * Edits an existing Post entity.
     *
     * @Template("KeltanasPageBundle:Post:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        /** @var Post $entity */
        $entity = $this->findOr404('KeltanasPageBundle:Post', $id);

        if (!$this->isGranted('ROLE_ADMIN') && ($entity->getAccount()->getId() !== $this->getUser()->getId())) {
           throw new AccessDeniedException("Post belongs to another author");
        }

        $editForm = $this->createForm(new PostType(), $entity)->submit($request);

        try {
            if ($editForm->isValid()) {
                $this->getEntityManager()->lock($entity, LockMode::OPTIMISTIC, $entity->getVersion());
                $this->getEntityManager()->persist($entity);
                $this->getEventDispatcher()->dispatch(PostEvent::POST_UPDATE, new PostEvent($this->getEntityManager(), $entity));
                $this->getEntityManager()->flush();

                return $this->redirectToPath('post_show', ['id' => $id]);
            }
        } catch(OptimisticLockException $e) {
            $this->addFlash('error', 'Sorry, but someone else has already changed this entity. Please apply the changes again!');
        }

        return ['entity' => $entity, 'edit_form' => $editForm->createView()];
    }

    /**
     * Deletes a Post entity.
     *
     * @Template()
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);

        /** @var Post $entity */
        $entity = $this->findOr404('KeltanasPageBundle:Post', $id);
        if (!$this->isGranted('ROLE_ADMIN') && ($entity->getAccount()->getId() !== $this->getUser()->getId())) {
            throw new AccessDeniedException("Post belongs to another author");
        }

        if ($request->isMethod('post') || $request->isMethod('delete')) {
            $form->submit($request);
            if ($form->isValid()) {
                $event = new PostEvent($this->getEntityManager(), $entity);
                $this->getEventDispatcher()->dispatch(PostEvent::POST_REMOVE, $event);
                $this->getEntityManager()->remove($entity);
                $this->getEntityManager()->flush();
                return $this->redirect($this->generateUrl('post'));
            }
        }

        return ['entity' => $entity, 'delete_form' => $form->createView()];
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function previewAction(Request $request)
    {
        /** @var MarkdownParserInterface $markdown */
        $markdown = $this->get('markdown.parser');
        $html = $markdown->transformMarkdown(str_replace('[cut]', '', $request->request->get('content')));

        return new Response($html);
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
