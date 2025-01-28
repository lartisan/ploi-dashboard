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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lartisan\PloiDashboard\Models\Server as ServerModel;
use Lartisan\PloiDashboard\Models\Site;
use Lartisan\PloiDashboard\Pages\BasePage;
use Livewire\Attributes\On;

class Server extends BasePage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-server';

    protected static ?string $activeNavigationIcon = 'heroicon-s-server';

    protected static string $view = 'ploi-dashboard::pages.server.server';

    protected static ?string $navigationGroup = 'Ploi Management';

    protected ?string $heading = 'Server';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'server';

    public ?ServerModel $record = null; // TODO: Change to SiteModel

    public array $data = [];

    public bool $showAdvancedSettings = false;

    public function mount(): void
    {
        $this->record = ServerModel::query()->first();
    }

    #[On('refresh')]
    private function getQuery(): Builder
    {
        return Site::query();
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

                                        /*Forms\Components\Checkbox::make('webserver_template')
                                            ->helperText('This will create an extra site where you do all the development on. After development is done you\'ll be able to push over the code to the main site.'),*/

                                        /*Forms\Components\Checkbox::make('system_user')
                                            ->helperText('This will enable you to redirect this whole domain, to another domain.'),*/
                                    ]),
                            ]),
                    ])
                    ->footerActions([
                        Forms\Components\Actions\Action::make('Add site')
                            ->action(function () {
                                try {
                                    // Ploi::make()->createQueueWorker($this->data);

                                    $this->sendNotification('success', 'Queue worker created successfully');
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
            ->query($this->getQuery())
            ->recordUrl(fn (Model $record) => route('filament.admin.pages.site'))
            ->columns([
                Tables\Columns\Layout\Split::make([
                    $this->getStatusColumn()->grow(false),

                    Tables\Columns\TextColumn::make('domain')
                        ->description(fn (Model $record): string => 'PHP Version: ' . $record->php_version)
                        ->searchable(),
                ]),
            ]);
    }
}
