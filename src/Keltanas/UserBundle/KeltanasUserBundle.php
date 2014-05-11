<?php

namespace Keltanas\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class KeltanasUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
