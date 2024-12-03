<x-dynamic-component
        :component="$getEntryWrapperView()"
        :entry="$entry"
>
    <div @class([
        'flex gap-6',
        'flex-col' => ($triggerPosition ?? '') === 'below',
        'items-center' => ($triggerPosition ?? '') !== 'below',
    ])>
        <pre>{{ $getState() }}</pre>

        <x-filament::modal :width="$width ?? 'lg'" :id="$action">
            <x-slot name="trigger">
                <x-filament::button size="xs">
                    {{ $triggerLabel }}
                </x-filament::button>
            </x-slot>

            <x-filament-panels::form wire:submit="{{ $action }}">
                {{ $this->$form() }}

                <x-filament-panels::form.actions
                        :actions="Arr::wrap($actionButton)"
                />
            </x-filament-panels::form>
        </x-filament::modal>
    </div>
</x-dynamic-component>