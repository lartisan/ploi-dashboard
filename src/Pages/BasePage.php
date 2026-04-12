<?php

namespace Lartisan\PloiDashboard\Pages;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\ViewField;
use Filament\Pages\Page;
use Filament\Tables;
use Illuminate\Support\HtmlString;
use Lartisan\PloiDashboard\Actions\NotificationMessage;

abstract class BasePage extends Page
{
    protected function sendNotification(string $type, string $message, ?string $body = null): void
    {
        NotificationMessage::make()->send($type, $message, $body);
    }

    protected function getStatusColumn(): string | Tables\Columns\ViewColumn
    {
        return Tables\Columns\ViewColumn::make('status')
            ->label(false)
            ->width('8px')
            ->view('ploi-dashboard::tables.columns.status');
    }

    protected function getFormHeadingViewField(string $name, ?string $heading = null, HtmlString | string | null $description = null, int $colSpan = 1): Field
    {
        return ViewField::make($name)
            ->view('ploi-dashboard::forms.fields.form-heading')
            ->viewData([
                'heading' => $heading,
                'description' => $description,
            ])
            ->columnSpan($colSpan);
    }
}
