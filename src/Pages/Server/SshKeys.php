<?php

namespace Lartisan\PloiDashboard\Pages\Server;

use Exception;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;

class SshKeys extends BasePage implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-clock';

    protected static string | null | \BackedEnum $activeNavigationIcon = 'heroicon-s-clock';

    protected string $view = 'ploi-dashboard::pages.server.ssh-keys';

    protected static string | null | \UnitEnum $navigationGroup = 'Ploi Management';

    protected static ?int $navigationSort = 5;

    protected ?string $heading = '';

    protected static ?string $navigationLabel = 'SSH Keys';

    protected static ?string $slug = 'server/ssh-keys';

    public array $data = [];

    public static function getNavigationParentItem(): ?string
    {
        return config('ploi-dashboard.enabled_modules.server.server') ? 'Server' : null;
    }

    public static function canAccess(): bool
    {
        return config('ploi-dashboard.enabled_modules.server.ssh-keys');
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('SSH keys')
                    ->columns(3)
                    ->schema([
                        $this->getFormHeadingViewField(
                            name: 'new_ssh_key',
                            heading: 'New SSH key',
                            description: 'Add your SSH key to your server here, this allows you to SSH into your server.',
                        ),

                        Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required(),

                                Forms\Components\Textarea::make('key')
                                    ->required()
                                    ->rows(3),
                            ]),
                    ])
                    ->footerActions([
                        Action::make('Add SSH Key')
                            ->label('Add SSH Key')
                            ->action(function ($state) {
                                try {
                                    Ploi::make()->createSshKey($state);

                                    $this->sendNotification('success', 'SSH Key created successfully');
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
            ->records(fn () => Ploi::make()->listSshKeys()->map(fn ($r) => $r->toLivewire()))
            ->poll(config('ploi-dashboard.polling.interval'))
            ->paginated(false)
            ->columns([
                Tables\Columns\Layout\Split::make([
                    $this->getStatusColumn()->grow(false),

                    Tables\Columns\TextColumn::make('name')
                        ->description(fn ($record) => sprintf('%s as system user', $record['system_user'])),
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
            ->action(function ($record) {
                try {
                    Ploi::make()->deleteSshKey($record['id']);

                    $this->sendNotification('success', 'SSH key deleted successfully');

                    sleep(1);
                    $this->dispatch('refresh');
                } catch (Exception $e) {
                    $this->sendNotification('warning', $e->getMessage());
                }
            });
    }
}
