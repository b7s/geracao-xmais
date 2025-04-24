@php
    $auth = auth('associado')->user();
@endphp
<x-filament-panels::page>
    {{ $this->form }}
    
    <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="flex flex-col md:flex-row gap-8">
            <div class="flex-1">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold">Meu Perfil</h2>
                    <a href="{{ route('filament.associado.pages.edit-profile') }}" class="filament-button filament-button-size-sm inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset bg-primary-600 dark:bg-primary-500 hover:bg-primary-500 dark:hover:bg-primary-400 focus:bg-primary-700 dark:focus:bg-primary-600 focus:ring-offset-primary-700 dark:ring-offset-primary-700 text-white shadow focus:ring-white border-transparent px-3">
                        <svg class="h-5 w-5 -ml-1 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Editar
                    </a>
                </div>
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Nome</div>
                            <div class="mt-1 text-gray-900 dark:text-gray-100">{{ $auth->nome }}</div>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Sobrenome</div>
                            <div class="mt-1 text-gray-900 dark:text-gray-100">{{ $auth->sobrenome }}</div>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Celular</div>
                            <div class="mt-1 text-gray-900 dark:text-gray-100">{{ $auth->celular }}</div>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Data de Nascimento</div>
                            <div class="mt-1 text-gray-900 dark:text-gray-100">{{ $auth->data_nascimento?->format('d/m/Y') }}</div>
                        </div>

                        @if($auth->whatsapp)
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">WhatsApp</div>
                            <div class="mt-1 text-gray-900 dark:text-gray-100">{{ $auth->whatsapp }}</div>
                        </div>
                        @endif

                        @if($auth->email)
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">E-mail</div>
                            <div class="mt-1 text-gray-900 dark:text-gray-100">{{ $auth->email }}</div>
                        </div>
                        @endif

                        @if($auth->instagram)
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Instagram</div>
                            <div class="mt-1 text-gray-900 dark:text-gray-100"><span>@</span>{{ $auth->instagram }}</div>
                        </div>
                        @endif
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Atividades</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Cartão Benefícios</div>
                                <div class="mt-1 text-gray-900 dark:text-gray-100">
                                    <span class="inline-flex items-center">
                                        @if($auth->cartao_beneficios)
                                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="ml-1.5">Sim</span>
                                        @else
                                            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="ml-1.5">Não</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Frequenta Eventos</div>
                                <div class="mt-1 text-gray-900 dark:text-gray-100">
                                    <span class="inline-flex items-center">
                                        @if($auth->frequenta_eventos)
                                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="ml-1.5">Sim</span>
                                        @else
                                            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="ml-1.5">Não</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            @if($auth->cartao_beneficios && $auth->cartao_beneficios_desde)
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Cartão Benefícios Desde</div>
                                <div class="mt-1 text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($auth->cartao_beneficios_desde)->format('m/Y') }}</div>
                            </div>
                            @endif

                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Grupo WhatsApp</div>
                                <div class="mt-1 text-gray-900 dark:text-gray-100">
                                    <span class="inline-flex items-center">
                                        @if($auth->grupo_whatsapp)
                                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="ml-1.5">Sim</span>
                                        @else
                                            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="ml-1.5">Não</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($auth->endereco)
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Endereço</h3>
                        <div class="text-gray-900 dark:text-gray-100">{{ $auth->endereco }}</div>
                    </div>
                    @endif

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Membro desde</div>
                        <div class="mt-1 text-gray-900 dark:text-gray-100">{{ $auth->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page> 