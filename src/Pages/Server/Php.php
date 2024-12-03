<?php

namespace Lartisan\PloiDashboard\Pages\Server;

use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\HtmlString;
use Lartisan\PloiDashboard\Models\Server as ServerModel;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Livewire\Attributes\On;

class Php extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $activeNavigationIcon = 'heroicon-s-code-bracket';

    protected static string $view = 'ploi-dashboard::pages.server.php';

    protected static ?string $navigationGroup = 'Ploi Management';

    protected static ?string $navigationParentItem = 'Server';

    protected static ?int $navigationSort = 1;

    protected ?string $heading = '';

    protected static ?string $navigationLabel = 'PHP';

    protected static ?string $slug = 'server/php';

    public ?ServerModel $record = null;

    public array $data = [];

    public array $availablePhpVersions = ['8.4', '8.3', '8.2', '8.1', '8.0', '7.4', '7.3', '7.2', '7.1', '7.0', '5.6'];

    public function mount(): void
    {
        $this->getRecord();
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
                Forms\Components\Section::make('OPcache')
                    ->columns(5)
                    ->schema([
                        $this->getFormHeadingViewField(
                            name: 'op_cache',
                            description: 'OPcache improves PHP performance by storing precompiled script bytecode in shared memory, thereby removing the need for PHP to load and parse scripts on each request. When you enable OPcache, make sure you reload the PHP FPM worker after each deploy to clear cache memory.',
                            colSpan: 3
                        ),

                        // Action when test domain is not enabled
                        Forms\Components\Group::make()
                            ->columnStart(5)
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('Reload PHP FPM worker')
                                        ->label('Reload PHP FPM worker')
                                        ->extraAttributes(fn () => ['class' => 'w-full'])
                                        ->action(fn () => null),

                                    Forms\Components\Actions\Action::make('Enable OPcache')
                                        ->label('Enable OPcache')
                                        ->extraAttributes(fn () => ['class' => 'w-full'])
                                        ->hidden(fn () => $this->record->opcache)
                                        ->action(function () {
                                            try {
                                                Ploi::make()->enableOpCache($this->record->id);

                                                $this->sendNotification('success', 'OPcache enabled successfully');
                                            } catch (Exception $e) {
                                                $this->sendNotification('warning', $e->getMessage());
                                            }
                                        })
                                        ->after(function () {
                                            sleep(1);
                                            $this->dispatch('refresh');
                                        }),

                                    Forms\Components\Actions\Action::make('Disable OPcache')
                                        ->label('Disable OPcache')
                                        ->color('danger')
                                        ->extraAttributes(fn () => ['class' => 'w-full'])
                                        ->visible(fn () => $this->record->opcache)
                                        ->action(function () {
                                            try {
                                                Ploi::make()->disableOpCache($this->record->id);

                                                $this->sendNotification('success', 'OPcache disabled successfully');
                                            } catch (Exception $e) {
                                                $this->sendNotification('warning', $e->getMessage());
                                            }
                                        })
                                        ->after(function () {
                                            sleep(1);
                                            $this->dispatch('refresh');
                                        }),
                                ]),
                            ]),
                    ]),

                Forms\Components\Section::make('PHP version')
                    ->columns()
                    ->schema([
                        $this->getFormHeadingViewField(
                            name: 'php_version',
                            description: $this->getDescription()
                        ),

                        // Action when test domain is not enabled
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Actions::make($this->getPhpVersionsActions()),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    private function getPhpVersionsActions(): array
    {
        $actions = [];

        foreach ($this->availablePhpVersions as $phpVersion) {
            $actions[] = Forms\Components\Actions\Action::make('Install ' . $phpVersion)
                ->label((in_array($phpVersion, $this->record->installed_php_versions) ? 'Installed ' : 'Install ') . $phpVersion)
                ->color(in_array($phpVersion, $this->record->installed_php_versions) ? 'gray' : 'primary')
                ->disabled(fn () => in_array($phpVersion, $this->record->installed_php_versions))
                ->action(function () use ($phpVersion) {
                    try {
                        Ploi::make()->installPhpVersion($this->record->id, $phpVersion);

                        $this->sendNotification('success', 'PHP version ' . $phpVersion . ' is being installed');
                    } catch (Exception $e) {
                        $this->sendNotification('warning', $e->getMessage());
                    }
                })
                ->after(function () {
                    sleep(1);
                    $this->dispatch('refresh');
                });
        }

        return $actions;
    }

    private function getDescription(): HtmlString
    {
        return new HtmlString('Your current default PHP version: ' . $this->record->php_version . '

            Your current PHP CLI version: ' . $this->record->php_cli_version . '
            
            You can install additional PHP versions right here. This will not change any websites but only install the PHP packages next to your current PHP version. You are also able to set the PHP CLI version to another version and the default PHP version that should be used for future websites that are created.
        ');
    }
}
