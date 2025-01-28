<?php

namespace Lartisan\PloiDashboard\Widgets\Repository;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Throwable;

class QuickDeploy extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'ploi-dashboard::widgets.repository.quick-deploy';

    public array $website;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('Enable Quick Deploy')
                        ->visible(fn () => true)
                        ->action(function () {
                            try {
                                // Ploi::make()->toggleQuickDeploy();
                                Notification::make()
                                    ->title('Quick Deploy enabled successfully')
                                    ->success()
                                    ->send();

                                // $this->sendNotification('success', 'Quick deploy has been enabled');
                            } catch (Throwable $e) {
                                // $this->sendNotification('danger', $e->getMessage());
                            }
                        }),
                ]),
            ])->statePath('website');
    }
}
