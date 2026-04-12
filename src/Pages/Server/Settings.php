<?php

namespace Lartisan\PloiDashboard\Pages\Server;

use Exception;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\ServerResponseData;

class Settings extends BasePage
{
    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string | null | \BackedEnum $activeNavigationIcon = 'heroicon-s-cog-6-tooth';

    protected string $view = 'ploi-dashboard::pages.server.settings';

    protected static string | null | \UnitEnum $navigationGroup = 'Ploi Management';

    protected static ?int $navigationSort = 7;

    protected ?string $heading = '';

    protected static ?string $slug = 'server/settings';

    public ?ServerResponseData $record = null;

    public array $data = [];

    public static function getNavigationParentItem(): ?string
    {
        return config('ploi-dashboard.enabled_modules.server.server') ? 'Server' : null;
    }

    public static function canAccess(): bool
    {
        return config('ploi-dashboard.enabled_modules.server.settings');
    }

    public function mount(): void
    {
        $this->record = Ploi::make()->getServer();

        $this->data = [
            'name' => $this->record->name,
            'ip_address' => $this->record->ip_address,
            'internal_ip' => $this->record->internal_ip,
            'ssh_port' => $this->record->ssh_port,
        ];
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Server information')
                    ->columns(3)
                    ->components([
                        $this->getFormHeadingViewField(
                            'new_queue',
                            description: 'Edit the server\'s details here.'
                        ),

                        Group::make()
                            ->columnSpan(2)
                            ->components([
                                Forms\Components\TextInput::make('name')
                                    ->label('Server name')
                                    ->required(),

                                Forms\Components\TextInput::make('ip_address')
                                    ->label('Server IP address')
                                    ->required(),

                                Forms\Components\TextInput::make('internal_ip')
                                    ->label('Server internal IP address'),

                                Forms\Components\TextInput::make('ssh_port')
                                    ->label('Server SSH port')
                                    ->required(fn (Get $get) => filled($get('ip_address'))),
                            ]),
                    ])
                    ->footerActions([
                        Action::make('Save')
                            ->action(function (array $state) {
                                $this->validate();

                                try {
                                    Ploi::make()->updateServer($state);

                                    $this->sendNotification('success', 'Server updated successfully');
                                } catch (Exception $e) {
                                    $this->sendNotification('warning', $e->getMessage());
                                }
                            }),
                    ])
                    ->footerActionsAlignment(Alignment::Right),
            ])
            ->statePath('data');
    }
}
