<?php

namespace Wychoong\ViewExtract\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Wychoong\ViewExtract\ViewExtract
 */
class ViewExtract extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wychoong\ViewExtract\ViewExtract::class;
    }
}
