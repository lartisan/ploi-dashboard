<?php

namespace Lartisan\PloiDashboard\Pages\Server;

use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;

class Logs extends BasePage implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string | null | \BackedEnum $activeNavigationIcon = 'heroicon-s-clipboard-document-list';

    protected string $view = 'ploi-dashboard::pages.server.logs';

    protected static string | null | \UnitEnum $navigationGroup = 'Ploi Management';

    protected static ?int $navigationSort = 6;

    protected ?string $heading = '';

    protected static ?string $slug = 'server/logs';

    public static function getNavigationParentItem(): ?string
    {
        return config('ploi-dashboard.enabled_modules.server.server') ? 'Server' : null;
    }

    public static function canAccess(): bool
    {
        return config('ploi-dashboard.enabled_modules.server.logs');
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->records(fn () => Ploi::make()->listLogs()->map(fn ($r) => $r->toLivewire()))
            ->poll(config('ploi-dashboard.polling.interval'))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->description(fn ($record) => $record['created_at']),
            ]);
    }
}
