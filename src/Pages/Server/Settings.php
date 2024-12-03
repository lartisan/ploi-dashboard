<?php

namespace Lartisan\PloiDashboard\Pages\Server;

use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Enums\Alignment;
use Lartisan\PloiDashboard\Models\Server as ServerModel;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;

class Settings extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $activeNavigationIcon = 'heroicon-s-cog-6-tooth';

    protected static string $view = 'ploi-dashboard::pages.server.settings';

    protected static ?string $navigationGroup = 'Ploi Management';

    protected static ?string $navigationParentItem = 'Server';

    protected static ?int $navigationSort = 7;

    protected ?string $heading = '';

    protected static ?string $slug = 'server/settings';

    public ?ServerModel $record = null; // TODO: Change to SiteModel

    public array $data = [];

    public function mount(): void
    {
        $this->data = ServerModel::query()->first()->toArray();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Server information')
                    ->columns(3)
                    ->schema([
                        $this->getFormHeadingViewField(
                            'new_queue',
                            description: 'Edit the server\'s details here.'
                        ),

                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
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
                                    ->required(fn (Forms\Get $get) => filled($get('ip_address'))),
                            ]),
                    ])
                    ->footerActions([
                        Forms\Components\Actions\Action::make('Save')
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
                    ->footerActionsAlignment(Alignment::Right)
            ])
            ->statePath('data')
        ;
    }
}
