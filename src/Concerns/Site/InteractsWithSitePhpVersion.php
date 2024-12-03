<?php

namespace Lartisan\PloiDashboard\Concerns\Site;

use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;

trait InteractsWithSitePhpVersion
{
    protected function changePhpVersionInfolistsComponent(): Infolists\Components\TextEntry
    {
        return Infolists\Components\TextEntry::make('php_version')
            ->view('ploi-dashboard::pages.site.partials.dynamic-modal-form')
            ->viewData([
                'triggerLabel' => 'Change PhpVersion', // Button label
                'form' => 'changePhpVersionForm', // Form fields
                'actionButton' => $this->changePhpVersionAction(), // Trigger action
                'action' => 'changePhpVersion', // Action triggered on form submission + the ID of the modal
            ])
            ->inlineLabel();
    }

    protected function changePhpVersionForm(): ?Forms\Form
    {
        return $this->makeForm()
            ->schema([
                Forms\Components\Select::make('website.php_version')
                    ->native(false)
                    ->label('PhpVersion')
                    ->required()
                    ->options(array_combine(
                        data_get($this->website, 'server.installed_php_versions'),
                        data_get($this->website, 'server.installed_php_versions')
                    ))
                    ->helperText('Select the new PHP Version for the site'),
            ]);
    }

    protected function changePhpVersionAction(): Actions\Action
    {
        return Actions\Action::make('Change PhpVersion')
            ->submit('Change PhpVersion');
    }

    public function changePhpVersion(): void
    {
        Ploi::make()->changePhpVersion(
            data_get($this->changePhpVersionForm()->getState(), 'website.php_version'),
        );

        Notification::make()
            ->title('PhpVersion changed successfully')
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'changePhpVersion');
    }
}
