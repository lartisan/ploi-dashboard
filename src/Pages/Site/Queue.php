<?php

namespace Lartisan\PloiDashboard\Pages\Site;

use Exception;
use Filament\Forms;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lartisan\PloiDashboard\Models\Queue as QueueModel;
use Lartisan\PloiDashboard\Models\Site;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Livewire\Attributes\On;

class Queue extends BasePage implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';
    protected static ?string $activeNavigationIcon = 'heroicon-s-code-bracket';

    protected static string $view = 'ploi-dashboard::pages.queue';

    protected static ?string $navigationGroup = 'Ploi Management';

    protected static ?string $navigationParentItem = 'Site';

    protected static ?int $navigationSort = 2;

    protected ?string $heading = '';

    protected static ?string $slug = 'site/queue';

    public array $website;
    public array $data;
    private Builder $query;

    public function mount(): void
    {
        $this->resetData();

        try {
            $this->website = Site::first()->load('server')->toArray();
        } catch (Exception $e) {
            $this->website = [];

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

    #[On('refresh')]
    public function getQuery(): ?Builder
    {
        try {
            return QueueModel::query();
        } catch (Exception $e) {
            $this->sendNotification('warning', $e->getMessage());

            return null;
        }
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Queue')
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
                                        if (! data_get($this->website, 'server.installed_php_versions')) {
                                            return [];
                                        }

                                        return array_combine(
                                            data_get($this->website, 'server.installed_php_versions'),
                                            data_get($this->website, 'server.installed_php_versions')
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
                    ->footerActionsAlignment(Alignment::Right)
            ])->statePath('data');
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query($this->getQuery())
            ->poll(config('ploi-dashboard.polling.interval'))
            ->columns([
                $this->getStatusColumn(),

                Tables\Columns\TextColumn::make('queue')
                    ->description(
                        fn (Model $record) => sprintf('Connection %s - Sleep %s - Processes %s', $record->connection, $record->sleep, $record->processes)
                    )
            ])
            ->actions([
                $this->pauseAction(),
                $this->restartAction(),
                $this->deleteAction(),
            ])
        ;
    }

    private function createQueueAction(): Forms\Components\Actions\Action
    {
        return Forms\Components\Actions\Action::make('Add queue')
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
            ->icon(fn (Model $record) => $record->status === 'active' ? 'heroicon-s-pause' : 'heroicon-s-play')
            ->color(fn (Model $record) => $record->status === 'active' ? 'gray' : 'success')
            ->iconButton()
            ->action(function (Model $record) {
                try {
                    $queue = Ploi::make()->pauseQueueWorker($record->id);
                    $record->update(['status' => $queue->status]);

                    $this->sendNotification(
                        'success',
                        sprintf('Queue worker %s successfully', $record->status === 'active' ? 'paused' : 'resumed')
                    );
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
            ->action(function (Model $record) {
                try {
                    Ploi::make()->restartQueueWorker($record->id);

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
            ->action(function (Model $record) {
                try {
                    Ploi::make()->deleteQueueWorker($record->id);

                    $this->sendNotification('success', 'Queue worker deleted successfully');

                    sleep(1);
                    $this->dispatch('refresh');
                } catch (Exception $e) {
                    $this->sendNotification('warning', $e->getMessage());
                }
            });
    }
}
