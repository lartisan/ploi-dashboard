<?php

namespace Lartisan\PloiDashboard\Pages\Server;

use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Lartisan\PloiDashboard\Models\Database;
use Lartisan\PloiDashboard\Models\Server as ServerModel;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Livewire\Attributes\On;

class Databases extends BasePage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $activeNavigationIcon = 'heroicon-s-circle-stack';

    protected static string $view = 'ploi-dashboard::pages.server.databases';

    protected static ?string $navigationGroup = 'Ploi Management';

    protected static ?int $navigationSort = 2;

    protected ?string $heading = '';

    protected static ?string $slug = 'server/databases';

    public ?ServerModel $record = null;

    public ?Collection $sites = null;

    public array $data = [];

    public ?int $databasesCount = null;

    public bool $showDatabaseUsers = false;

    public static function getNavigationParentItem(): ?string
    {
        return config('ploi-dashboard.enabled_modules.server.server') ? 'Server' : null;
    }

    public static function canAccess(): bool
    {
        return config('ploi-dashboard.enabled_modules.server.databases');
    }

    public function mount(): void
    {
        $this->getRecord();
        $this->databasesCount = $this->getQuery()->count();
        $this->sites = Ploi::make()->listSites();
    }

    #[On('refresh')]
    public function getQuery(): Builder
    {
        return Database::query();
    }

    #[On('refresh')]
    public function getRecord(): void
    {
        $this->record = ServerModel::query()->first();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Databases')
                    ->description($this->databasesCount . ' databases')
                    /*->headerActions([
                        Forms\Components\Actions\Action::make('toggle_database_users')
                            ->label(fn () => $this->showDatabaseUsers ? 'Show Databases' : 'Show Users')
                            ->color('gray')
                            ->action(function () {
                                $this->showDatabaseUsers = ! $this->showDatabaseUsers;
                            })
                    ])*/
                    ->columns(3)
                    ->schema(fn () => $this->getNewDatabaseSchemaFields())
                    ->footerActions([
                        Forms\Components\Actions\Action::make('Add database')
                            ->action(function ($state) {
                                try {
                                    Ploi::make()->createDatabase($state);

                                    $this->sendNotification('success', 'Database created successfully');

                                    sleep(1);
                                    $this->dispatch('refresh');
                                } catch (Exception $e) {
                                    $this->sendNotification('warning', $e->getMessage());
                                }
                            }),
                    ])
                    ->footerActionsAlignment(Alignment::Right),
            ])
            ->statePath('data');
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query($this->getQuery())
            ->poll(config('ploi-dashboard.polling.interval'))
            ->columns([
                $this->getStatusColumn(),

                Tables\Columns\TextColumn::make('name')
                    ->description(function (Model $record) {
                        $description = count($record->users) . str(' database user')->plural(count($record->users));

                        if ($domain = data_get($record->site, 'root_domain')) {
                            $description = str($description)->append(' &bull; attached to ')->append($domain);
                        }

                        return new HtmlString($description);
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('colone')
                        ->modalHeading('Clone database')
                        ->form([
                            Forms\Components\ViewField::make('warning')
                                ->view('ploi-dashboard::forms.fields.form-warning')
                                ->viewData([
                                    'text' => 'When you clone the database, users will not be cloned. You can create a new user for this cloned database.',
                                ]),

                            Forms\Components\TextInput::make('name')
                                ->formatStateUsing(fn (Model $record) => $record->name . '_clone')
                                ->required(),

                            Forms\Components\TextInput::make('user')
                                ->label('User (optional)'),

                            Forms\Components\TextInput::make('password')
                                ->label('Password (optional)')
                                ->hintAction(
                                    Forms\Components\Actions\Action::make('generatePassword')
                                        ->label('Generate')
                                        ->action(function (Forms\Set $set) {
                                            $set('password', Str::random(20));
                                        })
                                ),
                        ])
                        ->action(function (Model $record, $data) {
                            try {
                                Ploi::make()->cloneDatabase($record->id, $data);

                                $this->sendNotification('success', 'Database cloned successfully');

                                sleep(1);
                                $this->dispatch('refresh');
                            } catch (Exception $e) {
                                $this->sendNotification('warning', $e->getMessage());
                            }
                        }),

                    Tables\Actions\Action::make('delete')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Model $record) {
                            try {
                                Ploi::make()->deleteDatabase($record->id);

                                $this->sendNotification('success', 'Database deleted successfully');

                                sleep(1);
                                $this->dispatch('refresh');
                            } catch (Exception $e) {
                                $this->sendNotification('warning', $e->getMessage());
                            }
                        }),
                ]),
            ]);
    }

    private function getNewDatabaseSchemaFields(): array
    {
        $newDatabaseFields = [
            $this->getFormHeadingViewField('new_database', 'New database', 'Create a new database here.'),

            Forms\Components\Group::make()
                ->columnSpan(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required(),

                    Forms\Components\TextInput::make('user')
                        ->label('User (optional)'),

                    Forms\Components\TextInput::make('password')
                        ->label('Password (optional)')
                        ->hintAction(
                            Forms\Components\Actions\Action::make('generatePassword')
                                ->label('Generate')
                                ->action(function (Forms\Set $set) {
                                    $set('password', Str::random(20));
                                })
                        ),

                    Forms\Components\Textarea::make('description')
                        ->label('Description (optional)'),

                    Forms\Components\Select::make('site_id')
                        ->label('Site (optional)')
                        ->options($this->sites->pluck('domain', 'id')->toArray()),
                ]),
        ];

        return $this->showDatabaseUsers ? [] : $newDatabaseFields;
    }
}
