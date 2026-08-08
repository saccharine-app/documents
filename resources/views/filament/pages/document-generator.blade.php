<x-filament-panels::page>
    <form wire:submit="generatePdf">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" size="lg">
                Compile & Render PDF via Gotenberg
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>