<?php

namespace Lartisan\PloiDashboard;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Colors\Color;

class PloiDashboardPlugin implements Plugin
{
    public function getId(): string
    {
        return 'ploi-dashboard';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->colors([
                'blue' => Color::Blue,
            ])
            ->discoverPages(in: __DIR__.'/Pages', for: 'Lartisan\\PloiDashboard\\Pages')
            ->discoverWidgets(in: __DIR__.'/Widgets', for: 'Lartisan\\PloiDashboard\\Widgets')
        ;
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
