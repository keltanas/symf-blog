<?php

namespace keltanas\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class keltanasUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
