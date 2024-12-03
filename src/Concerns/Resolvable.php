<?php

namespace Lartisan\PloiDashboard\Concerns;

trait Resolvable
{
    public static function make(array $parameters = []): static
    {
        return app(static::class, $parameters);
    }
}
