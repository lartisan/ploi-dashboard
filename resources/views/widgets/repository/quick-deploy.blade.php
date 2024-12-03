<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between gap-x-3">
            <div class="flex flex-col gap-y-6">
                <div class="flex flex-row items-center gap-x-3">
                    <div class="flex-1">
                        <h2 class="grid flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white">
                            <h1 class="text-lg font-semibold">Quick deploy</h1>
                        </h2>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Quick deploy makes it possible to deploy your application once you push code. When you push to your installed branch, Ploi will run your deploy script for you.
                        </p>
                    </div>
                </div>
            </div>

            <div class="w-full flex justify-end">
                {{ $this->form }}
            </div>
            {{--<x-filament-panels::form wire:submit="enableQuickDeployAction">
                {{ $this->form }}

                <div class="flex justify-end">
                    <x-filament-panels::form.actions
                            :actions="$this->getFormActions()"
                    />
                </div>
            </x-filament-panels::form>--}}
        </div>
    </x-filament::section>
</x-filament-widgets::widget>