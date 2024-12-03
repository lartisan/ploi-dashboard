<?php

namespace Lartisan\PloiDashboard\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Lartisan\PloiDashboard\PloiDashboard
 */
class PloiDashboard extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Lartisan\PloiDashboard\PloiDashboard::class;
    }
}
