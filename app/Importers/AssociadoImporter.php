<?php

namespace App\Importers;

use App\Models\Associado;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AssociadoImporter extends Importer
{
    protected static ?string $model = Associado::class;

    protected static string $csvDelimiter = ';';

    public static function getCompletedNotificationBody(Import $import): string
    {
        $count = $import->successful_rows;
        return "Importação concluída! {$count} associados foram importados com sucesso.";
    }

    public function mutateState(array $state): array
    {
        Log::info('Importando associado', $state);
        return $state;
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->label('Nº')
                ->requiredMapping(false),

            ImportColumn::make('nome')
                ->label('NOME')
                ->rules(['required', 'string']), 

            ImportColumn::make('sobrenome')
                ->label('SOBRENOME')
                ->rules(['required', 'string']),

            ImportColumn::make('celular')
                ->label('CELULAR Whatsapp')
                ->rules(['required', 'string']),

            ImportColumn::make('email')
                ->label('E-mail')
                ->rules(['nullable', 'email']),

            ImportColumn::make('observacoes')
                ->label('OBSERVAÇÕES')
                ->rules(['nullable', 'string']),

            ImportColumn::make('frequenta_eventos')
                ->label('FREQUENTA OS EVENTOS')
                ->castStateUsing(fn (?string $state): bool => $state === '1')
                ->rules(['nullable', 'boolean']),

            ImportColumn::make('grupo_whatsapp')
                ->label('FAZ PARTE DO GRUPO DO WHATSAPP')
                ->castStateUsing(fn (?string $state): bool => $state === '1')
                ->rules(['nullable', 'boolean']),

            ImportColumn::make('instagram')
                ->label('INSTAGRAM')
                ->rules(['nullable', 'string']),

            ImportColumn::make('cartao_beneficios')
                ->label('CARTÃO BENEFÍCIOS')
                ->castStateUsing(fn (?string $state): bool => $state === '1')
                ->rules(['nullable', 'boolean']),

            ImportColumn::make('cartao_beneficios_desde')
                ->label('DESDE (MÊS/ANO)')
                ->castStateUsing(function (?string $state) {
                    if (empty($state)) {
                        return null;
                    }
                    
                    try {
                        return Carbon::createFromFormat('m/Y', $state)->format('Y-m-d');
                    } catch (\Exception $e) {
                        return null;
                    }
                })
                ->rules(['nullable', 'date']),

            ImportColumn::make('data_nascimento')
                ->label('DATA/NASCIMENTO')
                ->castStateUsing(function (?string $state) {
                    if (empty($state) || strpos($state, '/') === false) {
                        return null;
                    }
                    
                    try {
                        return Carbon::createFromFormat('d/m/Y', $state)->format('Y-m-d');
                    } catch (\Exception $e) {
                        return null;
                    }
                })
                ->rules(['nullable', 'date']),

            ImportColumn::make('endereco')
                ->label('ENDEREÇO')
                ->rules(['nullable', 'string']),
        ];
    }
} 