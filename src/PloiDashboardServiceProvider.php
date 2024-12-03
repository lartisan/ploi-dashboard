<?php

namespace Lartisan\PloiDashboard;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Lartisan\PloiDashboard\Testing\TestsPloiDashboard;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PloiDashboardServiceProvider extends PackageServiceProvider
{
    public static string $name = 'ploi-dashboard';

    public static string $viewNamespace = 'ploi-dashboard';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('lartisan/ploi-dashboard');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        // Testing
        Testable::mixin(new TestsPloiDashboard);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'lartisan/ploi-dashboard';
    }

    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('ploi-dashboard', __DIR__ . '/../resources/dist/components/ploi-dashboard.js'),
            Css::make('ploi-dashboard-styles', __DIR__ . '/../resources/dist/ploi-dashboard.css'),
            //Js::make('ploi-dashboard-scripts', __DIR__ . '/../resources/dist/ploi-dashboard.js'),
        ];
    }
}
