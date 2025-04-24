<x-filament-panels::page>
    {{ $this->form }}

    <div class="mt-4 flex items-center justify-end gap-x-3">
        <x-filament::button
            color="gray"
            tag="a"
            href="{{ route('filament.associado.pages.dashboard') }}"
        >
            Cancelar
        </x-filament::button>
        
        <x-filament::button
            type="submit"
            wire:click="save"
        >
            Salvar
        </x-filament::button>
    </div>
</x-filament-panels::page>
