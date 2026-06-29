<?php

namespace App\Demo;

class NoExecute
{
    public function ping(): string
    {
        exit('must not run when only showing source');

        return 'ok';
    }
}
