<x-filament-panels::page.simple>
    <div class="space-y-4">
        <div class="flex flex-col items-center justify-center">
            <img src="{{ asset('images/logo-geracao-xmais.png') }}" alt="Logo Geração X+" class="h-16 mb-2">
            <h2 class="text-xl font-bold py-3">Área do associado</h2>
        </div>

        <p class="mt-2 text-center text-gray-500 dark:text-gray-400">
            Digite seu celular e data de nascimento para acessar
        </p>

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
                pelo WhatsApp <a href="https://wa.me/55{{ config('app.whatsapp_admin') }}" target="_blank" class="underline">{{ preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', config('app.whatsapp_admin')) }}</a>
            </p>
        </div>
    </div>
</x-filament-panels::page.simple> 
