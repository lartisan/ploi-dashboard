<?php

namespace Lartisan\PloiDashboard\Pages\Server;

use Exception;
use Filament\Forms;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lartisan\PloiDashboard\Models\Daemon;
use Lartisan\PloiDashboard\Models\Log;
use Lartisan\PloiDashboard\Pages\BasePage;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Livewire\Attributes\On;

class Logs extends BasePage implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $activeNavigationIcon = 'heroicon-s-clipboard-document-list';

    protected static string $view = 'ploi-dashboard::pages.server.logs';

    protected static ?string $navigationGroup = 'Ploi Management';

    protected static ?string $navigationParentItem = 'Server';

    protected static ?int $navigationSort = 6;

    protected ?string $heading = '';

    protected static ?string $slug = 'server/logs';

    private Builder $query;

    #[On('refresh')]
    public function getQuery(): Builder
    {
        return Log::query();
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query($this->getQuery())
            ->poll(config('ploi-dashboard.polling.interval'))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->description(fn (Model $record) => $record->created_at),
            ]);
    }
}