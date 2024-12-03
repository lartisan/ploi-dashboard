<?php

namespace Lartisan\PloiDashboard\Concerns\Site;

use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;

trait InteractsWithSiteDeployScript
{
    protected function updateDeployScriptInfolistsComponent(): Infolists\Components\TextEntry
    {
        return Infolists\Components\TextEntry::make('deploy_script')
            ->view('ploi-dashboard::pages.site.partials.dynamic-modal-form')
            ->viewData([
                'triggerLabel' => 'Change PhpVersion', // Button label
                'triggerPosition' => 'below', // Button position
                'form' => 'updateDeployScriptForm', // Form fields
                'actionButton' => $this->updateDeployScriptAction(), // Trigger action
                'action' => 'updateDeployScript', // Action triggered on form submission + the ID of the modal
                'width' => '3xl', // Modal width
            ])
            ->inlineLabel();
    }

    protected function updateDeployScriptForm(): ?Forms\Form
    {
        return $this->makeForm()
            ->schema([
                Forms\Components\MarkdownEditor::make('website.deploy_script')
                    ->label('Deploy Script')
                    ->required()
                    ->helperText('The deploy_script that will be used when deploying the site.'),
            ]);
    }

    protected function updateDeployScriptAction(): Actions\Action
    {
        return Actions\Action::make('Update Deploy Script')
            ->submit('Update Deploy Script');
    }

    public function updateDeployScript(): void
    {
        Ploi::make()->updateDeployScript(
            data_get($this->updateDeployScriptForm()->getState(), 'website.deploy_script'),
        );

        Notification::make()
            ->title('Deploy Script updated successfully')
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'updateDeployScript');
    }
}
