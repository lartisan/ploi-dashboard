<?php

namespace Lartisan\PloiDashboard\Pages\Site;

use Exception;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\ServerResponseData;

class Queue extends BasePage implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-code-bracket';

    protected static string | null | \BackedEnum $activeNavigationIcon = 'heroicon-s-code-bracket';

    protected string $view = 'ploi-dashboard::pages.queue';

    protected static string | null | \UnitEnum $navigationGroup = 'Ploi Management';

    protected static ?int $navigationSort = 2;

    protected ?string $heading = '';

    protected static ?string $slug = 'site/queue';

    public ?ServerResponseData $server = null;

    public array $data;

    public static function getNavigationParentItem(): ?string
    {
        return config('ploi-dashboard.enabled_modules.site.site') ? 'Site' : null;
    }

    public static function canAccess(): bool
    {
        return config('ploi-dashboard.enabled_modules.site.queue');
    }

    public function mount(): void
    {
        $this->resetData();

        try {
            $this->server = Ploi::make()->getServer();
        } catch (Exception $e) {
            $this->server = null;

            $this->sendNotification('warning', $e->getMessage());
        }
    }

    protected function resetData(): void
    {
        $this->data = [
            'php_version' => null,
            'connection' => 'database',
            'queue' => 'default',
            'maximum_seconds' => 30,
            'sleep' => 10,
            'processes' => 1,
            'maximum_tries' => 3,
            'backoff' => 0,
            'memory' => 128,
            'enviroment' => 'production',
        ];
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Queue')
                    ->columns(3)
                    ->schema([
                        $this->getFormHeadingViewField(
                            name: 'new_queue',
                            heading: 'New Queue',
                            description: 'You can create queue workers here to process your jobs. This is especially useful when you have a Laravel project.',
                        ),

                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\Select::make('php_version')
                                    ->native(false)
                                    ->placeholder('PHP CLI Version (default)')
                                    ->options(function () {
                                        if (! $this->server?->installed_php_versions) {
                                            return [];
                                        }

                                        return array_combine(
                                            $this->server->installed_php_versions,
                                            $this->server->installed_php_versions
                                        );
                                    }),

                                Forms\Components\TextInput::make('connection'),

                                Forms\Components\TextInput::make('queue'),

                                Forms\Components\Group::make()
                                    ->columns()
                                    ->schema([
                                        Forms\Components\TextInput::make('maximum_seconds')
                                            ->label('Maximum seconds per job')
                                            ->helperText('The number of seconds a child process can run'),

                                        Forms\Components\TextInput::make('sleep')
                                            ->label('Sleep time')
                                            ->helperText('Number of seconds to sleep when no job is available'),
                                    ]),

                                Forms\Components\Group::make()
                                    ->columns()
                                    ->schema([
                                        Forms\Components\TextInput::make('processes')
                                            ->helperText('The number of processes to spawn'),

                                        Forms\Components\TextInput::make('maximum_tries')
                                            ->helperText('Number of times to attempt a job before logging it failed'),
                                    ]),

                                Forms\Components\Group::make()
                                    ->columns()
                                    ->schema([
                                        Forms\Components\TextInput::make('backoff')
                                            ->helperText('The number of seconds to wait before retrying a job that encountered an uncaught exception'),

                                        Forms\Components\TextInput::make('memory')
                                            ->helperText('The memory limit in MB (default 128MB)'),
                                    ]),

                                Forms\Components\TextInput::make('enviroment')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->footerActions([
                        $this->createQueueAction(),
                    ])
                    ->footerActionsAlignment(Alignment::Right),
            ])->statePath('data');
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->records(fn () => Ploi::make()->getQueueWorkers()->map(fn ($r) => $r->toLivewire()))
            ->poll(config('ploi-dashboard.polling.interval'))
            ->columns([
                $this->getStatusColumn(),

                Tables\Columns\TextColumn::make('queue')
                    ->description(
                        fn ($record) => sprintf('Connection %s - Sleep %s - Processes %s', $record['connection'], $record['sleep'], $record['processes'])
                    ),
            ])
            ->actions([
                $this->pauseAction(),
                $this->restartAction(),
                $this->deleteAction(),
            ]);
    }

    private function createQueueAction(): Action
    {
        return Action::make('Add queue')
            ->action(function () {
                try {
                    Ploi::make()->createQueueWorker($this->data);

                    $this->sendNotification('success', 'Queue worker created successfully');
                } catch (Exception $e) {
                    $this->sendNotification('warning', $e->getMessage());
                }
            })
            ->after(function () {
                $this->resetData();
                sleep(1);
                $this->dispatch('refresh');
            });
    }

    private function pauseAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('pause')
            ->icon(fn ($record) => $record['status'] === 'active' ? 'heroicon-s-pause' : 'heroicon-s-play')
            ->color(fn ($record) => $record['status'] === 'active' ? 'gray' : 'success')
            ->iconButton()
            ->action(function ($record) {
                try {
                    Ploi::make()->pauseQueueWorker($record['id']);

                    $this->sendNotification(
                        'success',
                        sprintf('Queue worker %s successfully', $record['status'] === 'active' ? 'paused' : 'resumed')
                    );

                    sleep(1);
                    $this->dispatch('refresh');
                } catch (Exception $e) {
                    $this->sendNotification('warning', $e->getMessage());
                }
            });
    }

    private function restartAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('restart')
            ->icon('heroicon-s-arrow-path')
            ->color('gray')
            ->iconButton()
            ->action(function ($record) {
                try {
                    Ploi::make()->restartQueueWorker($record['id']);

                    $this->sendNotification('success', 'Queue worker restarted successfully');
                } catch (Exception $e) {
                    $this->sendNotification('warning', $e->getMessage());
                }
            });
    }

    private function deleteAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('delete')
            ->icon('heroicon-s-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->iconButton()
            ->action(function ($record) {
                try {
                    Ploi::make()->deleteQueueWorker($record['id']);

                    $this->sendNotification('success', 'Queue worker deleted successfully');

                    sleep(1);
                    $this->dispatch('refresh');
                } catch (Exception $e) {
                    $this->sendNotification('warning', $e->getMessage());
                }
            });
    }
}
