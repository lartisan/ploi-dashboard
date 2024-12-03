<?php

namespace Lartisan\PloiDashboard\Pages\Server;

use Exception;
use Filament\Forms;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lartisan\PloiDashboard\Models\Daemon;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Livewire\Attributes\On;

class Daemons extends BasePage implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $activeNavigationIcon = 'heroicon-s-clock';

    protected static string $view = 'ploi-dashboard::pages.server.daemons';

    protected static ?string $navigationGroup = 'Ploi Management';

    protected static ?string $navigationParentItem = 'Server';

    protected static ?int $navigationSort = 6;

    protected ?string $heading = '';

    protected static ?string $slug = 'server/daemons';

    public array $data = [
        'processes' => 1,
        'system_user' => 'ploi',
    ];

    private Builder $query;

    #[On('refresh')]
    public function getQuery(): Builder
    {
        return Daemon::query();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Daemons')
                    ->columns(3)
                    ->schema([
                        $this->getFormHeadingViewField(
                            name: 'new_daemon',
                            heading: 'New daemon',
                            description: 'Daemons are used to keep processes alive on your server. It will configure a supervisor worker for you and keep it running. For example, if you have a NodeJS server that you want to keep running, you can configure this here, and Ploi will take care of the rest.',
                        ),

                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\TextInput::make('command')
                                    ->required(),

                                Forms\Components\TextInput::make('processes')
                                    ->default(1)
                                    ->required(),

                                Forms\Components\TextInput::make('directory')
                                    ->helperText('Optional to enter a directory'),

                                Forms\Components\Select::make('system_user')
                                    ->selectablePlaceholder(false)
                                    ->options([
                                        'ploi' => 'ploi (default)',
                                        'root' => 'root',
                                    ]),
                            ]),
                    ])
                    ->footerActions([
                        Forms\Components\Actions\Action::make('Add daemon')
                            ->action(function ($state) {
                                try {
                                    Ploi::make()->createDaemon($state);

                                    $this->sendNotification('success', 'Daemon created successfully');
                                } catch (Exception $e) {
                                    $this->sendNotification('warning', $e->getMessage());
                                }
                            })
                            ->after(function () {
                                sleep(1);
                                $this->dispatch('refresh');
                            }),
                    ])
                    ->footerActionsAlignment(Alignment::Right),
            ])->statePath('data');
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query($this->getQuery())
            ->poll(config('ploi-dashboard.polling.interval'))
            ->paginated(false)
            ->columns([
                Tables\Columns\Layout\Split::make([
                    $this->getStatusColumn()->grow(false),

                    Tables\Columns\TextColumn::make('command')
                        ->description(
                            fn (Model $record) => str('Running on user ')
                                ->append($record->system_user)
                                ->append(' with ')
                                ->append($record->processes)
                                ->append(' process')
                                ->plural($record->processes)
                        ),
                ]),
            ])
            ->actions([
                $this->deleteAction(),
            ]);
    }

    private function deleteAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('delete')
            ->icon('heroicon-s-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->iconButton()
            ->action(function (Model $record) {
                try {
                    Ploi::make()->deleteDaemon($record->id);

                    $this->sendNotification('success', 'Daemon deleted successfully');

                    sleep(1);
                    $this->dispatch('refresh');
                } catch (Exception $e) {
                    $this->sendNotification('warning', $e->getMessage());
                }
            });
    }
}
