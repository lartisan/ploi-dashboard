<?php

namespace Lartisan\PloiDashboard\Pages\Site;

use Carbon\Carbon;
use Exception;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\SiteResponseData;

class Certificate extends BasePage implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-lock-closed';

    protected static string | null | \BackedEnum $activeNavigationIcon = 'heroicon-s-lock-closed';

    protected string $view = 'ploi-dashboard::pages.certificate';

    protected static string | null | \UnitEnum $navigationGroup = 'Ploi Management';

    protected static ?int $navigationSort = 3;

    protected ?string $heading = '';

    protected static ?string $slug = 'site/certificate';

    public ?SiteResponseData $website = null;

    public array $data;

    public static function getNavigationParentItem(): ?string
    {
        return config('ploi-dashboard.enabled_modules.site.site') ? 'Site' : null;
    }

    public static function canAccess(): bool
    {
        return config('ploi-dashboard.enabled_modules.site.certificate');
    }

    public function mount(): void
    {
        try {
            $this->website = Ploi::make()->getSite();

            $this->data = [
                'type' => 'letsencrypt',
                'certificate' => $this->website->domain,
                'force' => false,
            ];
        } catch (Exception $e) {
            $this->sendNotification('warning', $e->getMessage());
        }
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('SSL')
                    ->columns(3)
                    ->schema([
                        $this->getFormHeadingViewField(
                            'new_certificate',
                            heading: 'New certificate',
                            description: 'Request a new Let\'s Encrypt certificate here. Make sure before you request a Let\'s Encrypt certificate to check whether your DNS is setup properly. Below here we provide a link to help you with setting this up.',
                        ),

                        Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->native(false)
                                    ->selectablePlaceholder(false)
                                    ->options([
                                        'letsencrypt' => 'Let\'s Encrypt',
                                        'zerossl' => 'ZeroSSL',
                                    ]),

                                Forms\Components\TextInput::make('certificate')
                                    ->label('Domain')
                                    ->helperText('You may separate multiple domains by using a comma'),

                                Forms\Components\Checkbox::make('force')
                                    ->label('Force request (skip DNS verification)'),
                            ]),
                    ])
                    ->footerActions([
                        Action::make('Add certificate')
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
            ->records(fn () => Ploi::make()->getCertificates()->map(fn ($r) => $r->toLivewire()))
            ->poll(config('ploi-dashboard.polling.interval'))
            ->columns([
                $this->getStatusColumn(),

                Tables\Columns\TextColumn::make('domain')
                    ->description(
                        fn ($record) => ($record['expires_at'] ?? null)
                            ? sprintf('Expires %s', Carbon::parse($record['expires_at'])->diffForHumans())
                            : ''
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
                    Ploi::make()->deleteCertificate($record['id']);

                    $this->sendNotification('success', 'Certificate deleted successfully');

                    sleep(1);
                    $this->dispatch('refresh');
                } catch (Exception $e) {
                    $this->sendNotification('warning', $e->getMessage());
                }
            });
    }
}
