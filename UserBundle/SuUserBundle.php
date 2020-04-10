<?php

namespace Su\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SuUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}

