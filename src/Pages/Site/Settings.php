<?php

namespace Lartisan\PloiDashboard\Pages\Site;

use Exception;
use Filament\Forms;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\HtmlString;
use Lartisan\PloiDashboard\Models\Server;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Livewire\Attributes\On;
use Throwable;

class Settings extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $activeNavigationIcon = 'heroicon-s-cog-6-tooth';

    protected static string $view = 'ploi-dashboard::pages.settings';

    protected static ?string $navigationGroup = 'Ploi Management';

    protected static ?int $navigationSort = 7;

    protected ?string $heading = '';

    protected static ?string $slug = 'site/settings';

    public Server $server;

    public array $data = [];

    public static function getNavigationParentItem(): ?string
    {
        return config('ploi-dashboard.enabled_modules.site.site') ? 'Site' : null;
    }

    public static function canAccess(): bool
    {
        return config('ploi-dashboard.enabled_modules.site.settings');
    }

    public function mount(): void
    {
        try {
            $this->server = Server::query()->first();

            $this->getData();
        } catch (Throwable $e) {
            $this->sendNotification('warning', $e->getMessage());
        }
    }

    #[On('refresh')]
    public function getData(): void
    {
        $this->data = Ploi::make()->getSite()->toLivewire();

        $this->form->fill($this->data);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                // Domain
                $this->getDomainSectionFields(),

                // Toggle Robots
                $this->getBlockRobotsSectionFields(),

                // Test Domain
                $this->getTestDomainSectionFields(),

                // PHO Version
                $this->getPhpVersionSectionFields(),

                // Delete Site
                $this->getDeleteSiteSectionFields(),
            ])->statePath('data');
    }

    private function getDomainSectionFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Settings')
            ->columns(3)
            ->schema([
                $this->getFormHeadingViewField(
                    name: 'site_domain',
                    heading: 'Site domain',
                    description: 'You can update your site domain here, please be aware of the fact that your cronjobs and all other scripts that are pointed to this directory <code>/home/ploi/' . $this->data['domain'] . '</code> will not be accessible anymore. We also automatically delete any SSL certificates that contains this domain name.',
                ),

                Forms\Components\Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Forms\Components\TextInput::make('domain'),
                    ]),
            ])
            ->footerActions([
                Forms\Components\Actions\Action::make('Save')
                    ->action(function () {
                        try {
                            Ploi::make()->updateSite([
                                'root_domain' => $this->data['domain'],
                            ]);

                            $this->sendNotification('success', 'Site updated successfully');
                        } catch (Exception $e) {
                            $this->sendNotification('warning', $e->getMessage());
                        }
                    }),
            ])
            ->footerActionsAlignment(Alignment::Right);
    }

    private function getBlockRobotsSectionFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Robots')
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->columnStart(2)
                    ->columnSpan(2)
                    ->schema([
                        Forms\Components\Checkbox::make('disable_robots')
                            ->helperText('This will add a header to your site to block the search engine robots. It is up to the search engines to honor this request.'),
                    ]),
            ])
            ->footerActions([
                Forms\Components\Actions\Action::make('Save')
                    ->action(function (array $state) {
                        try {
                            Ploi::make()->robotAccess([
                                'disable_robots' => $state['disable_robots'],
                            ]);

                            $this->sendNotification('success', 'Robots updated successfully');
                        } catch (Exception $e) {
                            $this->sendNotification('warning', $e->getMessage());
                        }
                    })
                    ->after(function () {
                        sleep(1);
                        $this->dispatch('refresh');
                    }),
            ])
            ->footerActionsAlignment(Alignment::Right);
    }

    private function getTestDomainSectionFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Test domain')
            ->columns(5)
            ->schema([
                $this->getFormHeadingViewField(
                    name: 'test_domain',
                    description: 'Test domain is a great way to try out your application before going live or switching your main domains DNS. You will get a URL from us to use to try out your application.
                                  Note that it could take up to a few minutes for DNS to propagate. We recommend to wait at least 10~15 minutes if it doesn\'t work before trying again.',
                    colSpan: 2
                ),

                // Action when test domain is not enabled
                Forms\Components\Group::make()
                    ->visible(fn () => $this->data['test_domain'] === null)
                    ->columnStart(5)
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('Enable test domain')
                                ->action(function () {
                                    try {
                                        Ploi::make()->enableTestDomain();

                                        $this->sendNotification('success', 'Test domain has been enabled');
                                    } catch (Throwable $e) {
                                        $this->sendNotification('danger', $e->getMessage());
                                    }
                                })
                                ->after(function () {
                                    sleep(1);

                                    $this->dispatch('refresh');
                                }),
                        ]),
                    ]),

                // Info when test domain is enabled
                Forms\Components\ViewField::make('test_domain_info')
                    ->visible(fn () => $this->data['test_domain'] !== null)
                    ->columnSpan(3)
                    ->view('ploi-dashboard::forms.fields.form-info')
                    ->viewData([
                        'text' => 'You currently have an SSL certificate set up which means you have a working domain.\n
                                           You cannot request a testing domain if you have a working domain.',
                    ]),
            ])
            ->footerActions([
                Forms\Components\Actions\Action::make('Disable test domain')
                    ->visible(fn () => $this->data['test_domain'] !== null)
                    ->color('danger')
                    ->action(function () {
                        try {
                            Ploi::make()->disableTestDomain();

                            $this->sendNotification('success', 'Test domain disabled successfully');
                        } catch (Exception $e) {
                            $this->sendNotification('warning', $e->getMessage());
                        }
                    })
                    ->after(function () {
                        sleep(1);

                        $this->dispatch('refresh');
                    }),
            ])
            ->footerActionsAlignment(Alignment::Right);
    }

    private function getPhpVersionSectionFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make('PHP Version ' . $this->data['php_version'])
            ->columns(5)
            ->schema([
                $this->getFormHeadingViewField(
                    name: 'test_domain',
                    description: 'You can change the used PHP version here for this site. This list displays all the installed PHP versions you have. If you need another version not listed here, you can head over to the PHP tab inside your server to install additional versions.',
                    colSpan: 3
                ),

                // Action when test domain is not enabled
                Forms\Components\Group::make()
                    // ->visible(fn() => $this->data['test_domain'] === null)
                    ->columnStart(5)
                    ->columnSpan(1)
                    ->schema($this->setPhpVersionsFromServer()),
            ]);
    }

    private function getDeleteSiteSectionFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Danger Zone')
            ->schema([
                // Danger Zone
                Forms\Components\Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\ViewField::make('warning')
                            ->view('ploi-dashboard::forms.fields.form-warning')
                            ->viewData([
                                'text' => 'You can remove your site here, your site will not be reachable anymore. Be aware that your site\'s files will be permanently deleted and cannot be retrieved.',
                            ]),
                    ]),
            ])
            ->footerActions([
                Forms\Components\Actions\Action::make('Delete')
                    ->color('danger')
                    ->modalWidth('md')
                    ->form([
                        Forms\Components\Placeholder::make('Are you sure you want to delete this site?')
                            ->content(new HtmlString('Please type <code>' . $this->data['domain'] . '</code> to confirm that you want to delete this site.'))
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('domain')
                            ->required()
                            ->rules(['required', 'in:' . $this->data['domain']]),
                    ])
                    ->action(function () {
                        try {
                            Ploi::make()->deleteSite();

                            $this->sendNotification('success', 'Site deleted successfully');
                        } catch (Throwable $e) {
                            $this->sendNotification('danger', $e->getMessage());
                        }
                    })
                    ->after(function () {
                        sleep(2);
                        $this->redirectRoute('filament.admin.pages.dashboard');
                    }),
            ])
            ->footerActionsAlignment(Alignment::Right);
    }

    private function setPhpVersionsFromServer(): array
    {
        $phpVersions = $this->server->installed_php_versions;

        $fields = [];

        foreach ($phpVersions as $version) {
            $fields[] = Forms\Components\Actions::make([
                Forms\Components\Actions\Action::make('PHP ' . $version)
                    ->label('PHP ' . $version)
                    ->extraAttributes(fn () => ['class' => 'w-full'])
                    ->disabled($this->data['php_version'] === $version)
                    ->action(function () use ($version) {
                        try {
                            Ploi::make()->changePhpVersion($version);

                            $this->sendNotification('success', 'PHP version updated successfully');
                        } catch (Exception $e) {
                            $this->sendNotification('warning', $e->getMessage());
                        }
                    })
                    ->after(function () {
                        sleep(1);
                        $this->dispatch('refresh');
                    }),
            ]);
        }

        return $fields;
    }
}
