<?php

namespace App\Traits;

trait FilamentRedirect
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
