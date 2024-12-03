@php $pollInterval = config('ploi-dashboard.polling.interval') @endphp

<x-filament-panels::page>

    <div wire:poll.{{$pollInterval}}="getRecord">
        {{ $this->form }}
    </div>

    {{ $this->table }}

</x-filament-panels::page>