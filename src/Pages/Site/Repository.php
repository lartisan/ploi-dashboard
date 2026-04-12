<?php

namespace Lartisan\PloiDashboard\Pages\Site;

use Exception;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Throwable;

class Repository extends BasePage
{
    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-share';

    protected static string | null | \BackedEnum $activeNavigationIcon = 'heroicon-s-share';

    protected string $view = 'ploi-dashboard::pages.repository';

    protected static string | null | \UnitEnum $navigationGroup = 'Ploi Management';

    protected static ?int $navigationSort = 5;

    protected ?string $heading = '';

    protected static ?string $slug = 'site/repository';

    public array $data;

    public static function getNavigationParentItem(): ?string
    {
        return config('ploi-dashboard.enabled_modules.site.site') ? 'Site' : null;
    }

    public static function canAccess(): bool
    {
        return config('ploi-dashboard.enabled_modules.site.repository');
    }

    public function mount(): void
    {
        try {
            $this->data = Ploi::make()->getRepository()->toLivewire();

            $this->form->fill($this->data);
        } catch (Exception $e) {
            $this->data = [];

            $this->sendNotification('warning', $e->getMessage());
        }
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                // Repository
                Section::make('Repository')
                    ->id('installRepository')
                    ->components([
                        // Branch
                        Group::make()
                            ->columnSpanFull()
                            ->columns(3)
                            ->components([
                                $this->getFormHeadingViewField(
                                    name: 'branch_field',
                                    heading: 'Branch',
                                    description: 'Ploi uses this branch to gather details of the latest commit when you deploy your application. You should verify that this branch matches the branch in your deployment script and the branch that is actually deployed on your server.',
                                ),

                                Group::make()
                                    ->columnSpan(2)
                                    ->components([
                                        Forms\Components\TextInput::make('repository.branch'),
                                    ]),
                            ]),

                        // Repository
                        Group::make()
                            ->columnSpanFull()
                            ->columns(3)
                            ->components([
                                $this->getFormHeadingViewField(
                                    name: 'repository_field',
                                    heading: 'Repository',
                                    description: 'Changing this will update the Git remote URL on your server. Your site will still be available as we do not perform a deployment. The changed Git repository should contain the same repository and history as the previous one.',
                                ),

                                Group::make()
                                    ->columnSpan(2)
                                    ->columns(3)
                                    ->components([
                                        Forms\Components\ViewField::make('warning')
                                            ->columnSpanFull()
                                            ->view('ploi-dashboard::forms.fields.form-info')
                                            ->viewData([
                                                'text' => 'Warning: Do not use this if you want to install a entirely different project on your site. If you want to install an entirely different project you should uninstall the repository and then install the new repository.',
                                            ]),

                                        Forms\Components\Radio::make('repository.provider')
                                            ->label('Type')
                                            ->options([
                                                'bitbucket' => 'Bitbucket',
                                                'github' => 'GitHub',
                                                'gitlab' => 'GitLab',
                                            ]),

                                        Group::make()
                                            ->columnSpan(2)
                                            ->components([
                                                Forms\Components\TextInput::make('name')
                                                    ->formatStateUsing(fn ($state) => sprintf('%s/%s', data_get($this->data, 'repository.user'), data_get($this->data, 'repository.name')))
                                                    ->label('Repository'),

                                                Forms\Components\TextInput::make('provider')
                                                    ->readonly()
                                                    ->formatStateUsing(fn ($state) => sprintf('%s - %s', data_get($this->data, 'repository.user'), ucfirst(data_get($this->data, 'repository.provider'))))
                                                    ->label('Source provider'),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->footerActions([
                        Action::make('Install Repository')
                            ->action(function (array $state) {
                                $repository = data_get($state, 'repository');

                                try {
                                    Ploi::make()->installRepository([
                                        'provider' => data_get($repository, 'provider'),
                                        'branch' => data_get($repository, 'branch'),
                                        'name' => sprintf('%s/%s', data_get($repository, 'user'), data_get($repository, 'name')),
                                    ]);

                                    $this->sendNotification('success', 'Repository created successfully');
                                } catch (Throwable $e) {
                                    $this->sendNotification('danger', $e->getMessage());
                                }
                            }),
                    ])
                    ->footerActionsAlignment(Alignment::Right),

                Section::make('Danger Zone')
                    ->components([
                        // Danger Zone
                        Group::make()
                            ->columnSpanFull()
                            ->components([
                                Forms\Components\ViewField::make('warning')
                                    // ->columnSpan(2)
                                    // ->columnStart(2)
                                    ->view('ploi-dashboard::forms.fields.form-warning')
                                    ->viewData([
                                        'text' => 'You can remove your repository here, be aware that all files will be removed as well.',
                                    ]),
                            ]),
                    ])
                    ->footerActions([
                        Action::make('Delete')
                            ->color('danger')
                            ->action(function () {
                                try {
                                    Ploi::make()->deleteRepository();

                                    $this->sendNotification('success', 'Repository deleted successfully');
                                } catch (Throwable $e) {
                                    $this->sendNotification('danger', $e->getMessage());
                                }
                            }),
                    ])
                    ->footerActionsAlignment(Alignment::Right),
            ])->statePath('data');
    }
}
