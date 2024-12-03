<?php

namespace Lartisan\PloiDashboard\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Illuminate\Support\HtmlString;
use Lartisan\PloiDashboard\Actions\NotificationMessage;

abstract class BasePage extends Page implements HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected function sendNotification(string $type, string $message, ?string $body = null): void
    {
        NotificationMessage::make()->send($type, $message, $body);
    }

    protected function getStatusColumn(): string|Tables\Columns\ViewColumn
    {
        return Tables\Columns\ViewColumn::make('status')
            ->label(false)
            ->width('8px')
            ->view('ploi-dashboard::tables.columns.status');
    }

    protected function getFormHeadingViewField(string $name, ?string $heading = null, HtmlString|string|null $description = null, int $colSpan = 1): Forms\Components\Field
    {
        return Forms\Components\ViewField::make($name)
            ->view('ploi-dashboard::forms.fields.form-heading')
            ->viewData([
                'heading' => $heading,
                'description' => $description,
            ])
            ->columnSpan($colSpan);
    }
}