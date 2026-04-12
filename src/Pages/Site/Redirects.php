<?php

namespace Lartisan\PloiDashboard\Pages\Site;

use Exception;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\HtmlString;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;

class Redirects extends BasePage implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string | null | \BackedEnum $activeNavigationIcon = 'heroicon-s-arrows-right-left';

    protected string $view = 'ploi-dashboard::pages.redirects';

    protected static string | null | \UnitEnum $navigationGroup = 'Ploi Management';

    protected static ?int $navigationSort = 6;

    protected ?string $heading = '';

    protected static ?string $slug = 'site/redirects';

    public array $data = [
        'redirect_from' => '',
        'redirect_to' => '',
        'type' => 'redirect',
    ];

    public static function getNavigationParentItem(): ?string
    {
        return config('ploi-dashboard.enabled_modules.site.site') ? 'Site' : null;
    }

    public static function canAccess(): bool
    {
        return config('ploi-dashboard.enabled_modules.site.redirects');
    }

    public function mount(): void
    {
        $this->form->fill($this->data);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Redirects')
                    ->columns(3)
                    ->components([
                        $this->getFormHeadingViewField(
                            name: 'new_certificate',
                            heading: 'New redirect',
                            description: $this->getDescription(),
                        ),

                        Group::make()
                            ->columnSpan(2)
                            ->components([
                                Forms\Components\TextInput::make('redirect_from'),

                                Forms\Components\TextInput::make('redirect_to'),

                                Forms\Components\Select::make('type')
                                    ->native(false)
                                    ->selectablePlaceholder(false)
                                    ->options([
                                        'redirect' => 'Temporary (302)',
                                        'permanent' => 'Permanent (301)',
                                    ]),
                            ]),
                    ])
                    ->footerActions([
                        Action::make('Add redirect')
                            ->action(function () {
                                try {
                                    Ploi::make()->createRedirect($this->data);

                                    $this->sendNotification('success', 'Redirect created successfully');

                                    sleep(1);
                                    $this->dispatch('refresh');
                                } catch (Exception $e) {
                                    $this->sendNotification('warning', $e->getMessage());
                                }
                            }),
                    ])
                    ->footerActionsAlignment(Alignment::Right),
            ])->statePath('data');
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->records(fn () => Ploi::make()->getRedirects()->map(fn ($r) => $r->toLivewire()))
            ->poll(config('ploi-dashboard.polling.interval'))
            ->columns([
                $this->getStatusColumn(),

                Tables\Columns\TextColumn::make('redirect_from')
                    ->label('Redirect')
                    ->formatStateUsing(fn ($record): string => sprintf('Redirect %s to %s', $record['redirect_from'] ?? '', $record['redirect_to'] ?? ''))
                    ->description(
                        fn ($record): string => ($record['type'] ?? '') === 'permanent' ? 'Permanent (301)' : 'Temporary (302)'
                    ),
            ])
            ->recordActions([
                $this->deleteAction(),
            ]);
    }

    private function deleteAction(): Action
    {
        return Action::make('delete')
            ->icon('heroicon-s-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->iconButton()
            ->action(function ($record) {
                try {
                    Ploi::make()->deleteRedirect($record['id']);

                    $this->sendNotification('success', 'Redirect deleted successfully');

                    sleep(1);
                    $this->dispatch('refresh');
                } catch (Exception $e) {
                    $this->sendNotification('warning', $e->getMessage());
                }
            });
    }

    private function getDescription(): HtmlString
    {
        return new HtmlString(
            'You can create redirects here to have specific paths redirected to a new path.\n
            To redirect this whole domain to another domain, use these variables:\n
            From: <code>/(?!\.well-known/)(.*)</code>
            To: <code>https://example.com/$1</code>'
        );
    }
}
