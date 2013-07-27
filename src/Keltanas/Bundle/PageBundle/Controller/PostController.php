<?php

namespace Keltanas\Bundle\PageBundle\Controller;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Keltanas\Bundle\PageBundle\Entity\Post;
use Keltanas\Bundle\PageBundle\Form\PostType;
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

        $entities = $em->getRepository('KeltanasPageBundle:Post')->findAll();

        return $this->render('KeltanasPageBundle:Post:index.html.twig', array(
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
            $entity->setContentHtml($this->getMarkdown()->transformMarkdown($entity->getContentMd()));
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('post_show', array('id' => $entity->getId())));
        }

        return $this->render('KeltanasPageBundle:Post:new.html.twig', array(
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

        return $this->render('KeltanasPageBundle:Post:new.html.twig', array(
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

        $entity = $em->getRepository('KeltanasPageBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        return $this->render('KeltanasPageBundle:Post:show.html.twig', array(
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
        $entity = $em->getRepository('KeltanasPageBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        if (!$this->$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            if ($entity->getAccount()->getId() !== $this->getUser()->getId()) {
                throw new AccessDeniedException("Post belongs to another author");
            }
        }

        $editForm = $this->createForm(new PostType(), $entity);

        return $this->render('KeltanasPageBundle:Post:edit.html.twig', array(
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
        $entity = $em->getRepository('KeltanasPageBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        if (!$this->$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            if ($entity->getAccount()->getId() !== $this->getUser()->getId()) {
                throw new AccessDeniedException("Post belongs to another author");
            }
        }

        $editForm = $this->createForm(new PostType(), $entity);
        $editForm->submit($request);

        try {
            if ($editForm->isValid()) {
                $em->lock($entity, LockMode::OPTIMISTIC, $entity->getVersion());
                $entity->setContentHtml($this->getMarkdown()->transformMarkdown($entity->getContentMd()));
                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('post_show', array('id' => $id)));
            }
        } catch(OptimisticLockException $e) {
            $this->getFlashBag()
                ->add('error', "Sorry, but someone else has already changed this entity. Please apply the changes again!");
        }

        return $this->render('KeltanasPageBundle:Post:edit.html.twig', array(
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
        $entity = $em->getRepository('KeltanasPageBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        if (!$this->$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            if ($entity->getAccount()->getId() !== $this->getUser()->getId()) {
                throw new AccessDeniedException("Post belongs to another author");
            }
        }

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('KeltanasPageBundle:Post')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Post entity.');
            }

            $em->remove($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('post'));
        }

        return $this->render('KeltanasPageBundle:Post:delete.html.twig', array(
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

}
