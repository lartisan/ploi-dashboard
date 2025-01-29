<?php

namespace Lartisan\PloiDashboard\Pages\Server;

use Exception;
use Filament\Forms;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lartisan\PloiDashboard\Models\NetworkRule;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Livewire\Attributes\On;

class Network extends BasePage implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $activeNavigationIcon = 'heroicon-s-clock';

    protected static string $view = 'ploi-dashboard::pages.server.network';

    protected static ?string $navigationGroup = 'Ploi Management';

    protected static ?int $navigationSort = 4;

    protected ?string $heading = '';

    protected static ?string $slug = 'server/network';

    public array $data = [
        'type' => 'tcp',
        'rule_type' => 'allow',
    ];

    private Builder $query;

    public static function getNavigationParentItem(): ?string
    {
        return config('ploi-dashboard.enabled_modules.server.server') ? 'Server' : null;
    }

    public static function canAccess(): bool
    {
        return config('ploi-dashboard.enabled_modules.server.network');
    }

    #[On('refresh')]
    public function getQuery(): Builder
    {
        return NetworkRule::query();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Network')
                    ->columns(3)
                    ->schema([
                        $this->getFormHeadingViewField(
                            name: 'new_cronjob',
                            heading: 'New network rule',
                            description: 'You can manage your firewall rules here, if you have a additional service to open a port for like a socket server, this is the place to do so.',
                        ),

                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required(),

                                Forms\Components\TextInput::make('port')
                                    ->required()
                                    ->helperText('You may enter a port range e.g. 5000:6000'),

                                Forms\Components\Radio::make('type')
                                    ->label('Protocol')
                                    ->required()
                                    ->inline()
                                    ->inlineLabel(false)
                                    ->options([
                                        'tcp' => 'TCP',
                                        'udp' => 'UDP',
                                    ])
                                    ->default('tcp'),

                                Forms\Components\Radio::make('rule_type')
                                    ->label('Allow type')
                                    ->required()
                                    ->inline()
                                    ->inlineLabel(false)
                                    ->options([
                                        'allow' => 'Allow',
                                        'deny' => 'Deny',
                                    ])
                                    ->default('allow'),

                                Forms\Components\TextInput::make('from_ip_address')
                                    ->helperText('You may enter an IP range to allow multiple IP\'s e.g. 10.0.0.0/24, you may also define multiple IP addresses by separating them with a comma.'),

                                Forms\Components\Textarea::make('description')
                                    ->label('Description (optional)')
                                    ->helperText('You may optionally add a description here to recognize your firewall rule')
                                    ->rows(3),
                            ]),
                    ])
                    ->footerActions([
                        Forms\Components\Actions\Action::make('Add rule')
                            ->action(function ($state) {
                                try {
                                    Ploi::make()->createNetworkRule($state);

                                    $this->sendNotification('success', 'Rule created successfully');
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

                    Tables\Columns\TextColumn::make('name')
                        ->description(fn (Model $record) => sprintf('%s %s', $record->rule_type, $record->port)),
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
                    Ploi::make()->deleteNetworkRule($record->id);

                    $this->sendNotification('success', 'Rule deleted successfully');

                    sleep(1);
                    $this->dispatch('refresh');
                } catch (Exception $e) {
                    $this->sendNotification('warning', $e->getMessage());
                }
            });
    }
}
