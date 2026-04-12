<?php

namespace Lartisan\PloiDashboard\Pages\Server;

use Exception;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\ServerResponseData;

class Databases extends BasePage implements HasTable
{
    use InteractsWithTable;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-circle-stack';

    protected static string | null | \BackedEnum $activeNavigationIcon = 'heroicon-s-circle-stack';

    protected string $view = 'ploi-dashboard::pages.server.databases';

    protected static string | null | \UnitEnum $navigationGroup = 'Ploi Management';

    protected static ?int $navigationSort = 2;

    protected ?string $heading = '';

    protected static ?string $slug = 'server/databases';

    public ?ServerResponseData $record = null;

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
        $this->record = Ploi::make()->getServer();
        $this->databasesCount = Ploi::make()->getDatabases()->count();
        $this->sites = Ploi::make()->listSites();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Forms\Components\Section::make('Databases')
                    ->description($this->databasesCount . ' databases')
                    ->columns(3)
                    ->components(fn () => $this->getNewDatabaseSchemaFields())
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
            ->records(fn () => Ploi::make()->getDatabases()->map(fn ($r) => $r->toLivewire()))
            ->poll(config('ploi-dashboard.polling.interval'))
            ->columns([
                $this->getStatusColumn(),

                Tables\Columns\TextColumn::make('name')
                    ->description(function ($record) {
                        $users = $record['users'] ?? [];
                        $description = count($users) . str(' database user')->plural(count($users));

                        if ($domain = data_get($record['site'] ?? [], 'root_domain')) {
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
                                ->formatStateUsing(fn ($record) => ($record['name'] ?? '') . '_clone')
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
                        ->action(function ($record, $data) {
                            try {
                                Ploi::make()->cloneDatabase($record['id'], $data);

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
                        ->action(function ($record) {
                            try {
                                Ploi::make()->deleteDatabase($record['id']);

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
                ->components([
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
