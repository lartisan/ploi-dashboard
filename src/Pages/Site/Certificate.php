<?php

namespace Lartisan\PloiDashboard\Pages\Site;

use Exception;
use Filament\Forms;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lartisan\PloiDashboard\Models\Certificate as CertificateModel;
use Lartisan\PloiDashboard\Models\Site;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Livewire\Attributes\On;

class Certificate extends BasePage implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $activeNavigationIcon = 'heroicon-s-lock-closed';

    protected static string $view = 'ploi-dashboard::pages.certificate';

    protected static ?string $navigationGroup = 'Ploi Management';

    protected static ?string $navigationParentItem = 'Site';

    protected static ?int $navigationSort = 3;

    protected ?string $heading = '';

    protected static ?string $slug = 'site/certificate';

    public ?Site $website;

    public array $data;

    private Builder $query;

    public function mount(): void
    {
        try {
            $this->website = Site::first();

            $this->data = [
                'type' => 'letsencrypt',
                'certificate' => $this->website->domain,
                'force' => false,
            ];
        } catch (Exception $e) {
            $this->sendNotification('warning', $e->getMessage());
        }
    }

    #[On('refresh')]
    public function getQuery(): Builder
    {
        return CertificateModel::query();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('SSL')
                    ->columns(3)
                    ->schema([
                        $this->getFormHeadingViewField(
                            'new_certificate',
                            heading: 'New certificate',
                            description: 'Request a new Let\'s Encrypt certificate here. Make sure before you request a Let\'s Encrypt certificate to check whether your DNS is setup properly. Below here we provide a link to help you with setting this up.',
                        ),

                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->native(false)
                                    ->selectablePlaceholder(false)
                                    ->options([
                                        'letsencrypt' => 'Let\'s Encrypt',
                                        'zerossl' => 'ZeroSSL',
                                        // 'existing' => 'Install existing certificate',
                                        // 'signing' => 'Create signing request',
                                    ]),

                                Forms\Components\TextInput::make('certificate')
                                    ->label('Domain')
                                    ->helperText('You may separate multiple domains by using a comma'),

                                Forms\Components\Checkbox::make('force')
                                    ->label('Force request (skip DNS verification)'),
                            ]),
                    ])
                    ->footerActions([
                        Forms\Components\Actions\Action::make('Add certificate')
                            ->action(function () {
                                try {
                                    Ploi::make()->createCertificate($this->data);

                                    $this->sendNotification('success', 'Certificate created successfully');

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

                Tables\Columns\TextColumn::make('domain')
                    ->description(
                        fn (Model $record) => $record->expires_at ? sprintf('Expires %s', $record->expires_at?->diffForHumans()) : ''
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
                    Ploi::make()->deleteCertificate($record->id);

                    $this->sendNotification('success', 'Certificate deleted successfully');

                    sleep(1);
                    $this->dispatch('refresh');
                } catch (Exception $e) {
                    $this->sendNotification('warning', $e->getMessage());
                }
            });
    }
}
