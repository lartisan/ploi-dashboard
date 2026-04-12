<?php

namespace Lartisan\PloiDashboard\Pages\Server;

use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\ServerResponseData;

class Server extends BasePage implements HasTable
{
    use InteractsWithTable;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-server';

    protected static string | null | \BackedEnum $activeNavigationIcon = 'heroicon-s-server';

    protected string $view = 'ploi-dashboard::pages.server.server';

    protected static string | null | \UnitEnum $navigationGroup = 'Ploi Management';

    protected ?string $heading = 'Server';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'server';

    public ?ServerResponseData $record = null;

    public array $data = [];

    public bool $showAdvancedSettings = false;

    public static function canAccess(): bool
    {
        return config('ploi-dashboard.enabled_modules.server.server');
    }

    public function mount(): void
    {
        $this->record = Ploi::make()->getServer();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('New site')
                    ->columns(3)
                    ->components([
                        $this->getFormHeadingViewField(
                            'new_queue',
                            description: 'Create a new site in your server here, we offer more options if you press Advanced settings, you can select a system user there or setup staging.'
                        ),

                        Group::make()
                            ->columnSpan(2)
                            ->components([
                                TextInput::make('root_domain')
                                    ->label('Domain')
                                    ->required(),

                                Group::make()
                                    ->visible(fn () => $this->showAdvancedSettings)
                                    ->components([
                                        TextInput::make('web_directory')
                                            ->required(),

                                        TextInput::make('project_root')
                                            ->label('Project directory')
                                            ->required(),

                                        Select::make('project_type')
                                            ->native(false)
                                            ->required()
                                            ->selectablePlaceholder(false)
                                            ->options([
                                                null => 'None (Static HTML or PHP)',
                                                'laravel' => 'Laravel',
                                                'nodejs' => 'NodeJS',
                                                'statamic' => 'Statamic',
                                                'craft-cms' => 'Craft CMS',
                                                'symfony' => 'Symfony',
                                                'wordpress' => 'WordPress',
                                                'octobercms' => 'OctoberCMS',
                                                'cakephp' => 'CakePHP 3',
                                            ]),

                                        Checkbox::make('system_user')
                                            ->helperText('This will create a system user with a random generated name. You can also create a custom system user in the manage tab.'),
                                    ]),
                            ]),
                    ])
                    ->footerActions([
                        Action::make('Add site')
                            ->action(function () {
                                try {
                                    // Ploi::make()->createSite($this->data);

                                    $this->sendNotification('success', 'Site created successfully');
                                } catch (Exception $e) {
                                    $this->sendNotification('warning', $e->getMessage());
                                }
                            })
                            ->after(function () {
                                sleep(1);
                                $this->dispatch('refresh');
                            }),

                        Action::make('Advanced settings')
                            ->color('gray')
                            ->action(fn () => $this->showAdvancedSettings = ! $this->showAdvancedSettings),
                    ])
                    ->footerActionsAlignment(Alignment::Right),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn () => Ploi::make()->listSites()->map(fn ($r) => $r->toLivewire()))
            ->recordUrl(fn ($record) => route('filament.admin.pages.site'))
            ->columns([
                Tables\Columns\Layout\Split::make([
                    $this->getStatusColumn()->grow(false),

                    Tables\Columns\TextColumn::make('domain')
                        ->description(fn ($record): string => 'PHP Version: ' . $record['php_version'])
                        ->searchable(),
                ]),
            ]);
    }
}
