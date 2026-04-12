<?php

namespace Lartisan\PloiDashboard\Pages\Site;

use Exception;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\ServerResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\SiteResponseData;

class Site extends BasePage
{
    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-globe-alt';

    protected static string | null | \BackedEnum $activeNavigationIcon = 'heroicon-s-globe-alt';

    protected string $view = 'ploi-dashboard::pages.general';

    protected static string | null | \UnitEnum $navigationGroup = 'Ploi Management';

    protected static ?string $navigationLabel = 'Site';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'site';

    public ?SiteResponseData $site = null;

    public ?ServerResponseData $server = null;

    public array $data;

    public array $deployScriptVariables;

    public static function canAccess(): bool
    {
        return config('ploi-dashboard.enabled_modules.site.site');
    }

    public function mount(): void
    {
        $this->deployScriptVariables = $this->getDeployScriptVariables();

        try {
            $this->site = Ploi::make()->getSite();
            $this->server = Ploi::make()->getServer();
            $this->data = ['deploy_script' => $this->site->deploy_script];

            $this->form->fill($this->data);
        } catch (Exception $e) {
            $this->site = null;
            $this->server = null;
            $this->data = [];

            $this->sendNotification('warning', $e->getMessage());
        }
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Deployment')
                    ->components([
                        Forms\Components\MarkdownEditor::make('deploy_script')
                            ->toolbarButtons([]),
                    ])
                    ->footerActions([
                        $this->saveDeployScriptAction(),
                        $this->getDeployScriptsVariablesAction(),
                    ])
                    ->footerActionsAlignment(Alignment::Right),
            ])
            ->statePath('data');
    }

    public function getTitle(): string | Htmlable
    {
        return $this->site?->domain ?? 'General';
    }

    public function getSubheading(): Htmlable | string | null
    {
        return $this->site
            ? str('Last deploy: ')
                ->append($this->site->last_deploy_at)
                ->append('<br>')
                ->append('Server: ')
                ->append($this->server?->name)
                ->append(' | IP: ')
                ->append($this->server?->ip_address)
                ->append(' | Uptime: ')
                ->append($this->server?->uptime_human)
                ->append('<br>')
                ->append('PHP Version: ')
                ->append($this->server?->php_version)
                ->append(' | MySql Version: ')
                ->append($this->server?->mysql_version)
                ->append(' | Reboot required: ')
                ->append($this->server?->reboot_required ? 'Yes' : 'No')
                ->toHtmlString()
            : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Edit environment')
                ->color('gray')
                ->form(function () {
                    try {
                        $data = Ploi::make()->getEnvironment();

                        return [
                            Forms\Components\MarkdownEditor::make('content')
                                ->toolbarButtons()
                                ->default($data)
                                ->required(),
                        ];
                    } catch (Exception $e) {
                        $this->sendNotification('warning', $e->getMessage());
                    }

                    return [];
                })
                ->action(function ($data) {
                    try {
                        Ploi::make()->updateEnvironment($data);

                        $this->sendNotification('success', 'Environment updated successfully');
                    } catch (Exception $e) {
                        $this->sendNotification('warning', $e->getMessage());
                    }
                }),

            Action::make('Deploy')
                ->action(function () {
                    try {
                        $response = Ploi::make()->deploySite();

                        $this->sendNotification('success', data_get($response, 'message'));
                    } catch (Exception $e) {
                        $this->sendNotification('warning', $e->getMessage());
                    }
                }),
        ];
    }

    private function getDeployScriptVariables(): array
    {
        return json_decode(
            File::get(__DIR__ . '/../../../resources/data/deploy-scripts-variables-data.json'),
            true
        );
    }

    private function saveDeployScriptAction(): ?Action
    {
        return Action::make('Save')
            ->action(function (array $state) {
                try {
                    Ploi::make()->updateDeployScript(
                        data_get($state, 'deploy_script'),
                    );

                    $this->sendNotification('success', 'Deploy Script updated successfully');
                } catch (Exception $e) {
                    $this->sendNotification('warning', $e->getMessage());
                }
            });
    }

    private function getDeployScriptsVariablesAction(): ?Action
    {
        return Action::make('Deploy script variables')
            ->color(false)
            ->modalHeading('Deploy script variables')
            ->modalDescription('Think of deploy script variables as little helpers when you\'re setting up your site. They let you fill in specific details about your site without needing to write them into the code every time. This way, if something changes, like where your site is hosted, you just update the variable, and your script is good to go. It\'s an easy way to make your setup smoother, more flexible, and save you some time along the way.')
            ->modalFooterActions(fn () => [])
            ->extraAttributes(['class' => 'underline hover:no-underline'])
            ->form([
                Section::make('General')
                    ->columnSpanFull()
                    ->heading(new HtmlString('<p>General &bull; <span class="text-gray-600">Like <code>{DATE}</code> to get the current date</span></p>'))
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        Forms\Components\ViewField::make('General')
                            ->columnSpanFull()
                            ->view('ploi-dashboard::static.deploy-script-variables-table')
                            ->viewData(['rows' => $this->deployScriptVariables['general']]),
                    ]),

                Section::make('Site')
                    ->heading(new HtmlString('<p>Site &bull; <span class="text-gray-600">Like <code>{SITE_DOMAIN}</code> to get the current domain</span></p>'))
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        Forms\Components\ViewField::make('General')
                            ->columnSpanFull()
                            ->view('ploi-dashboard::static.deploy-script-variables-table')
                            ->viewData(['rows' => $this->deployScriptVariables['site']]),
                    ]),

                Section::make('Repository')
                    ->heading(new HtmlString('<p>Repository &bull; <span class="text-gray-600">Like <code>{BRANCH}</code> to get the current installed branch</span></p>'))
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        Forms\Components\ViewField::make('General')
                            ->columnSpanFull()
                            ->view('ploi-dashboard::static.deploy-script-variables-table')
                            ->viewData(['rows' => $this->deployScriptVariables['repository']]),
                    ]),
            ]);
    }
}
