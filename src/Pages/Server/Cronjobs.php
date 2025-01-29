<?php

namespace Lartisan\PloiDashboard\Pages\Server;

use Exception;
use Filament\Forms;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Lartisan\PloiDashboard\Models\Cronjob;
use Lartisan\PloiDashboard\Models\Site;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Livewire\Attributes\On;

class Cronjobs extends BasePage implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $activeNavigationIcon = 'heroicon-s-clock';

    protected static string $view = 'ploi-dashboard::pages.server.cronjobs';

    protected static ?string $navigationGroup = 'Ploi Management';

    protected static ?int $navigationSort = 3;

    protected ?string $heading = '';

    protected static ?string $slug = 'server/cronjobs';

    public array $data;

    private Builder $query;

    public static function getNavigationParentItem(): ?string
    {
        return config('ploi-dashboard.enabled_modules.server.server') ? 'Server' : null;
    }

    public static function canAccess(): bool
    {
        return config('ploi-dashboard.enabled_modules.server.cronjobs');
    }

    public function mount(): void
    {
        try {
            $this->data = [
                'command' => sprintf('php /home/ploi/%s/artisan schedule:run', Site::first()->domain),
                'user' => 'ploi',
                'frequency' => '* * * * *',
            ];
        } catch (Exception $e) {
            $this->sendNotification('warning', $e->getMessage());
        }
    }

    #[On('refresh')]
    public function getQuery(): Builder
    {
        return Cronjob::query();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Cronjobs')
                    ->columns(3)
                    ->schema([
                        $this->getFormHeadingViewField(
                            name: 'new_cronjob',
                            heading: 'New cronjob',
                            description: 'Cronjobs are used to execute tasks on a interval, this makes it easy to automate repetitive tasks.',
                        ),

                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\TextInput::make('command')
                                    ->helperText(new HtmlString('When using PHP, you may use aliases to use another PHP version, like for example <code>php8.0</code> instead of <code>php</code>.')),

                                Forms\Components\TextInput::make('user'),

                                Forms\Components\Radio::make('frequency')
                                    ->label(fn (Forms\Get $get, $state) => sprintf('Frequency (%s)', $state))
                                    ->live()
                                    ->options([
                                        '* * * * *' => 'Every minute',
                                        '0 * * * *' => 'Hourly',
                                        '0 2 * * *' => 'Nightly (02:00 AM)',
                                        '0 0 * * 0' => 'Weekly',
                                        '0 0 1 * *' => 'Monthly',
                                        'custom' => 'Custom',
                                    ]),

                                Forms\Components\TextInput::make('custom_frequency')
                                    ->label('Custom frequency')
                                    ->live()
                                    ->placeholder('* * * * *')
                                    ->visible(fn (Forms\Get $get) => $get('frequency') === 'custom')
                                    ->helperText('min | hour | day/month | month | day/week'),

                                Forms\Components\Textarea::make('description')
                                    ->label('Description (optional)')
                                    ->rows(3),
                            ]),
                    ])
                    ->footerActions([
                        Forms\Components\Actions\Action::make('Add cronjob')
                            ->action(function () {
                                try {
                                    $data = data_get($this->data, 'frequency') === 'custom'
                                        ? array_merge(
                                            $this->data,
                                            ['frequency' => data_get($this->data, 'custom_frequency')]
                                        )
                                        : $this->data;

                                    Ploi::make()->createCronjob($data);

                                    $this->sendNotification('success', 'Cronjob created successfully');
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
            ->columns([
                $this->getStatusColumn(),

                Tables\Columns\TextColumn::make('command')
                    ->description(function (Model $record) {
                        $description = sprintf('%s via user %s', $record->frequency, $record->user);

                        if ($record->description) {
                            $description .= sprintf(' - %s', $record->description);
                        }

                        return $description;
                    }),
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
                    Ploi::make()->deleteCronjob($record->id);

                    $this->sendNotification('success', 'Cronjob deleted successfully');

                    sleep(1);
                    $this->dispatch('refresh');
                } catch (Exception $e) {
                    $this->sendNotification('warning', $e->getMessage());
                }
            });
    }
}
