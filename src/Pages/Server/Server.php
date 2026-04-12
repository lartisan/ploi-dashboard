<?php

namespace Lartisan\PloiDashboard\Pages\Server;

use Exception;
use Filament\Forms;
use Filament\Forms\Form;
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('New site')
                    ->columns(3)
                    ->schema([
                        $this->getFormHeadingViewField(
                            'new_queue',
                            description: 'Create a new site in your server here, we offer more options if you press Advanced settings, you can select a system user there or setup staging.'
                        ),

                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\TextInput::make('root_domain')
                                    ->label('Domain')
                                    ->required(),

                                Forms\Components\Group::make()
                                    ->visible(fn () => $this->showAdvancedSettings)
                                    ->schema([
                                        Forms\Components\TextInput::make('web_directory')
                                            ->required(),

                                        Forms\Components\TextInput::make('project_root')
                                            ->label('Project directory')
                                            ->required(),

                                        Forms\Components\Select::make('project_type')
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

                                        Forms\Components\Checkbox::make('system_user')
                                            ->helperText('This will create a system user with a random generated name. You can also create a custom system user in the manage tab.'),
                                    ]),
                            ]),
                    ])
                    ->footerActions([
                        Forms\Components\Actions\Action::make('Add site')
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

                        Forms\Components\Actions\Action::make('Advanced settings')
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
            ->records(fn () => Ploi::make()->listSites())
            ->recordUrl(fn ($record) => route('filament.admin.pages.site'))
            ->columns([
                Tables\Columns\Layout\Split::make([
                    $this->getStatusColumn()->grow(false),

                    Tables\Columns\TextColumn::make('domain')
                        ->description(fn ($record): string => 'PHP Version: ' . $record->php_version)
                        ->searchable(),
                ]),
            ]);
    }
}
