@props(['record'])

<div class="bg-white dark:bg-gray-800 rounded-lg">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-3">
        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Cartão Benefícios</div>
            <div class="mt-1 text-gray-900 dark:text-gray-100">
                <span class="inline-flex items-center">
                    @if($record->cartao_beneficios)
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-2">Sim</span>
                    @else
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-2">Não</span>
                    @endif
                </span>
            </div>
        </div>

        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Frequenta Eventos</div>
            <div class="mt-1 text-gray-900 dark:text-gray-100">
                <span class="inline-flex items-center">
                    @if($record->frequenta_eventos)
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-2">Sim</span>
                    @else
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-2">Não</span>
                    @endif
                </span>
            </div>
        </div>

        @if($record->cartao_beneficios && $record->cartao_beneficios_desde)
        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Cartão Benefícios Desde</div>
            <div class="mt-1 text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($record->cartao_beneficios_desde)->format('m/Y') }}</div>
        </div>
        @endif

        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Grupo WhatsApp</div>
            <div class="mt-1 text-gray-900 dark:text-gray-100">
                <span class="inline-flex items-center">
                    @if($record->grupo_whatsapp)
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-2">Sim</span>
                    @else
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-2">Não</span>
                    @endif
                </span>
            </div>
        </div>

        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Membro desde</div>
            <div class="mt-1 text-gray-900 dark:text-gray-100">{{ $record->membro_desde ? $record->membro_desde->format('d/m/Y') : $record->created_at->format('d/m/Y') }}</div>
        </div>
    </div>
</div> 