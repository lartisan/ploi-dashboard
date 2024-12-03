<?php

namespace Lartisan\PloiDashboard\Concerns\Site;

use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;

trait InteractsWithSiteDomain
{
    protected function changeDomainInfolistsComponent(): Infolists\Components\TextEntry
    {
        return Infolists\Components\TextEntry::make('domain')
            ->view('ploi-dashboard::pages.site.partials.dynamic-modal-form')
            ->viewData([
                'triggerLabel' => 'Change Domain', // Button label
                'form' => 'changeDomainForm', // Form fields
                'actionButton' => $this->changeDomainAction(), // Trigger action
                'action' => 'changeDomain', // Action triggered on form submission + the ID of the modal
            ])
            ->inlineLabel();
    }

    protected function changeDomainForm(): ?Forms\Form
    {
        return $this->makeForm()
            ->schema([
                Forms\Components\TextInput::make('website.domain')
                    ->label('Domain')
                    ->required()
                    ->helperText('The domain name of the site'),
            ]);
    }

    protected function changeDomainAction(): Actions\Action
    {
        return Actions\Action::make('Change Domain')
            ->submit('Change Domain');
    }

    public function changeDomain(): void
    {
        Ploi::make()->updateSite(
            ['root_domain' => data_get($this->changeDomainForm()->getState(), 'website.domain')],
        );

        Notification::make()
            ->title('Domain changed successfully')
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'changeDomain');
    }
}
