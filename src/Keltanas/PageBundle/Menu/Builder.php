<?php
/**
 *
 * @author: Nikolay Ermin <keltanas@gmail.com>
 */

namespace Keltanas\PageBundle\Menu;


use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');

        $menu->addChild($this->trans('Blog'), [
                'route' => 'keltanas_page_homepage'
            ]);
//        $menu->addChild('Portfolio', [
//                'route' => 'Keltanas_page_portfolio',
//            ]);
        $menu->addChild($this->trans('About Me'), [
                'route' => 'keltanas_page_about',
            ]);

        return $menu;
    }

    protected function trans($message)
    {
        return $this->container->get('translator')->trans($message);
    }
}
