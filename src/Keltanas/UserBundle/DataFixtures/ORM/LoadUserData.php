<?php
/**
 * This file is part of the @package@.
 *
 * @author: Nikolay Ermin <keltanas@gmail.com>
 * @version: @version@
 */

namespace Keltanas\UserBundle\DataFixtures\ORM;


use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Doctrine\UserManager;
use Keltanas\Common\DataFixtures\AbstractFixture;

class LoadUserData extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('fos_user.user_manager');

        $user = $userManager->createUser();
        $user->addRole('ROLE_ADMIN');
        $user->setUsername('admin');
        $user->setEmail('admin@ermin.ru');
        $user->setPlainPassword('123456');
        $user->setEnabled(true);
        $userManager->updateUser($user);

        $this->addReference('User:admin', $user);

        $user = $userManager->createUser();
        $user->addRole('ROLE_ADMIN');
        $user->addRole('ROLE_SUPER_ADMIN');
        $user->setUsername('keltanas');
        $user->setEmail('keltanas@gmail.com');
        $user->setPlainPassword('123456');
        $user->setEnabled(true);
        $userManager->updateUser($user);

        $this->addReference('User:keltanas', $user);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}
