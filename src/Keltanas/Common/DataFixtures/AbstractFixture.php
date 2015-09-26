<?php
/**
 * This file is part of the @package@.
 *
 * @author: Nikolay Ermin <keltanas@gmail.com>
 * @version: @version@
 */

namespace Keltanas\Common\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture as BaseAbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class AbstractFixture extends BaseAbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param $object
     * @param $data
     * @param ObjectManager $manager For persistence
     * @param string $referenceName
     *
     * @return $object
     */
    protected function add($object, $data, ObjectManager $manager = null, $referenceName = null)
    {
        $accessor = new PropertyAccessor();
        foreach ($data as $key => $value) {
            if ($accessor->isWritable($object, $key)) {
                $accessor->setValue($object, $key, $value);
            }
        }

        if (null !== $manager) {
            $manager->persist($object);
            $manager->flush();
        }

        if (null !== $referenceName) {
            $this->addReference($referenceName, $object);
        }

        return $object;
    }
}
