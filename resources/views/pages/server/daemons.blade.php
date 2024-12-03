<x-filament-panels::page>

    {{ $this->form }}

    @if ($this->table->getRecords()->isNotEmpty())
        {{ $this->table }}
    @endif

</x-filament-panels::page>
