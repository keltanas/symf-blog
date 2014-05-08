<?php
/**
 * @link: http://blog.lavoie.sl/2012/10/automatic-cache-busting-using-git-in-symfony2.html
 */
$container->loadFromExtension('framework', array(
    'templating' => array(
        'assets_version' => exec('git rev-parse --short HEAD'),
    ),
));
