<x-filament-panels::page.simple>
    <x-slot name="title">
        Login Área do Associado
    </x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-center">
            <img src="{{ asset('images/logo-geracao-xmais.png') }}" alt="Logo Geração X+" class="h-16 mb-4">
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-center">
            Acesso Exclusivo para Associados
        </h1>
        <p class="mt-2 text-center text-gray-500 dark:text-gray-400">
            Digite seu celular e data de nascimento para acessar
        </p>
    </x-slot>

    <div class="space-y-4">
        <form wire:submit="authenticate" class="space-y-4">
            {{ $this->form }}

            <x-filament::button
                type="submit"
                class="w-full"
                color="success"
            >
                Entrar
            </x-filament::button>
        </form>

        <div class="text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Em caso de dúvidas, entre em contato com a administração<br>
                pelo WhatsApp (11) 99999-9999
            </p>
        </div>
    </div>
</x-filament-panels::page.simple> 