<?php

namespace Lartisan\PloiDashboard\Pages\Site;

use Exception;
use Filament\Forms;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Lartisan\PloiDashboard\Models\Redirect as RedirectModel;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Livewire\Attributes\On;

class Redirects extends BasePage implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $activeNavigationIcon = 'heroicon-s-arrows-right-left';

    protected static string $view = 'ploi-dashboard::pages.redirects';

    protected static ?string $navigationGroup = 'Ploi Management';

    protected static ?string $navigationParentItem = 'Site';

    protected static ?int $navigationSort = 6;

    protected ?string $heading = '';

    protected static ?string $slug = 'site/redirects';

    private Builder $query;

    public array $data = [
        'redirect_from' => '',
        'redirect_to' => '',
        'type' => 'redirect',
    ];

    public function mount(): void
    {
        $this->form->fill($this->data);
    }

    #[On('refresh')]
    public function getQuery(): Builder
    {
        return RedirectModel::query();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Redirects')
                    ->columns(3)
                    ->schema([
                        $this->getFormHeadingViewField(
                            name: 'new_certificate',
                            heading: 'New redirect',
                            description: $this->getDescription(),
                        ),

                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
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
                        Forms\Components\Actions\Action::make('Add redirect')
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
            ->query($this->getQuery())
            ->poll(config('ploi-dashboard.polling.interval'))
            ->columns([
                $this->getStatusColumn(),

                Tables\Columns\TextColumn::make('redirect_from')
                    ->label('Redirect')
                    ->formatStateUsing(fn (?Model $record): string => sprintf('Redirect %s to %s', $record?->redirect_from, $record?->redirect_to))
                    ->description(
                        fn (Model $record): string => $record->type === 'permanent' ? 'Permanent (301)' : 'Temporary (302)'
                    ),
            ])
            ->actions([
                $this->deleteAction(),
            ]);
    }

    private function deleteAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('delete')
            ->icon('heroicon-s-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->iconButton()
            ->action(function (Model $record) {
                try {
                    Ploi::make()->deleteRedirect($record->id);

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
