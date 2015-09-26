<?php
/**
 * This file is part of the @package@.
 *
 * @author: Nikolay Ermin <keltanas@gmail.com>
 * @version: @version@
 */

namespace Keltanas\Common;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as SymfonyController;
use Knp\Component\Pager\Paginator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Controller extends SymfonyController
{
    /**
     * @return Paginator
     */
    protected function getKnpPaginator()
    {
        return $this->get('knp_paginator');
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return EventDispatcher
     */
    protected function getEventDispatcher()
    {
        return $this->get('event_dispatcher');
    }

    /**
     * @return FlashBag
     */
    protected function getFlashBag()
    {
        return $this->get('session')->getFlashBag();
    }

    /**
     * Adds a flash message for type.
     *
     * @param string $type
     * @param string $message
     */
    protected function addFlash($type, $message)
    {
        $this->getFlashBag()->add($type, $message);
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied object.
     *
     * @param mixed $attributes
     * @param mixed $object
     *
     * @return bool
     */
    protected function isGranted($attributes, $object = null)
    {
        return $this->get('security.context')->isGranted($attributes, $object);
    }

    /**
     * Finds an Entity by its identifier.
     *
     * @param string $entityName
     * @param mixed $id
     * @param integer $lockMode
     * @param integer $lockVersion
     *
     * @throws
     * @return object
     */
    protected function findOr404($entityName, $id)
    {
        if (!($entity = $this->getEntityManager()->find($entityName, $id))) {
            throw $this->createNotFoundException(sprintf('Unable to find "%s" entity with id %d.', $entityName, $id));
        }

        return $entity;
    }

    /**
     * @param string         $route         The name of the route
     * @param mixed          $parameters    An array of parameters
     * @param bool|string    $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToPath($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->redirect($this->generateUrl($route, $parameters, $referenceType));
    }
}
