<header class="fi-section-header flex flex-col gap-3 px-6 py-4">
    <div class="flex items-center justify-between gap-3">
        <div @class([
            'grid flex-1 gap-y-1',
            'w-1/2' => isset($action),
        ])>
            @isset($heading)
                <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    {{ $heading }}
                </h3>
            @endisset

            @isset($description)
                <p class="fi-section-header-description overflow-hidden break-words text-sm text-gray-500 dark:text-gray-400">
                    {!! nl2br($description) !!}
                </p>
            @endisset
        </div>

        @isset($action)
            <div class="w-1/2 grid flex-1 gap-y-1 justify-end">
                <x-filament::button wire:click="{{ $action }}">
                    {{ $actionLabel }}
                </x-filament::button>
            </div>
        @endisset
    </div>
</header>