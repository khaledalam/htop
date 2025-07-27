<?php

namespace Htop\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \VendorName\Skeleton\Skeleton
 */
class Htop extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \VendorName\Skeleton\Skeleton::class;
    }
}
