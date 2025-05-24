<?php

namespace App\Filament\Resources\AssociadoResource\Pages;

use App\Filament\Resources\AssociadoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\IconPosition;
use App\Importers\AssociadoImporter;
use App\Models\Associado;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Illuminate\Support\Collection;
use Filament\Forms;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ListAssociados extends ListRecords
{
    use WithFileUploads;

    public $csvFile;
    public $importSessionId;

    protected static string $resource = AssociadoResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            AssociadoResource\Widgets\AssociadosStatsOverview::class,
        ];
    }

    protected function detectCsvSeparator(string $path): string
    {
        $handle = fopen($path, 'r');
        $semicolonCount = 0;
        $commaCount = 0;
        $lineCount = 0;
        $maxLines = 50;

        while (($line = fgets($handle)) !== false && $lineCount < $maxLines) {
            // Skip empty lines
            if (trim($line) === '') {
                continue;
            }

            $semicolonCount += substr_count($line, ';');
            $commaCount += substr_count($line, ',');
            $lineCount++;
        }

        fclose($handle);

        // If no separators found, default to semicolon
        if ($semicolonCount === 0 && $commaCount === 0) {
            return ';';
        }

        // Return the separator with more occurrences, defaulting to semicolon if equal
        return $semicolonCount >= $commaCount ? ';' : ',';
    }

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make('exportar')
                ->label('Exportar Associados')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->exports([
                    ExcelExport::make()
                        ->fromModel(Associado::class)
                        ->withFilename('associados-' . now()->format('Y-m-d'))
                        ->withColumns([
                            \pxlrbt\FilamentExcel\Columns\Column::make('nome')
                                ->heading('Nome'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('sobrenome')
                                ->heading('Sobrenome'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('celular')
                                ->heading('Celular'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('data_nascimento')
                                ->heading('Data de Nascimento')
                                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('m/Y') : ''),
                            \pxlrbt\FilamentExcel\Columns\Column::make('email')
                                ->heading('E-mail'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('instagram')
                                ->heading('Instagram'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('frequenta_eventos')
                                ->heading('Frequenta Eventos')
                                ->formatStateUsing(fn ($state) => $state ? 'Sim' : 'Não'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('grupo_whatsapp')
                                ->heading('Grupo WhatsApp')
                                ->formatStateUsing(fn ($state) => $state ? 'Sim' : 'Não'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('cartao_beneficios')
                                ->heading('Cartão Benefícios')
                                ->formatStateUsing(fn ($state) => $state ? 'Sim' : 'Não'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('endereco')
                                ->heading('Endereço'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('observacoes')
                                ->heading('Observações'),
                        ])
                ]),

            Actions\Action::make('importar-associados')
                ->label('Importar Associados')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('csvFile')
                        ->label('Arquivo CSV')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel'])
                        ->required(),
                    \Filament\Forms\Components\Fieldset::make('Modelo de Importação')
                        ->schema([
                            \Filament\Forms\Components\Placeholder::make('')
                                ->content(fn () => 'Baixe o modelo CSV para preencher com os dados dos associados.')
                                ->columnSpanFull(),

                            \Filament\Forms\Components\View::make('filament.components.download-model-button')
                                ->viewData([
                                    'url' => asset('modelos/modelo-importacao-associados.csv'),
                                    'label' => 'Baixar modelo CSV',
                                ]),
                        ]),
                ])
                ->action(function (array $data) {
                    $file = $data['csvFile'];
                    $path = storage_path('app/public/' . $file);
                    
                    // Detect the CSV separator
                    $separator = $this->detectCsvSeparator($path);
                    
                    // Configuração para importação em chunks
                    $chunkSize = 100; // Processa 100 registros por vez
                    
                    // Primeiro, contamos o total de linhas para feedback
                    $lineCount = 0;
                    $f = fopen($path, 'r');
                    while (!feof($f)) {
                        $line = fgets($f);
                        if (!empty($line)) {
                            $lineCount++;
                        }
                    }
                    fclose($f);
                    $totalRows = $lineCount - 1; // Remove o cabeçalho da contagem
                    
                    // Abre o arquivo novamente para processamento
                    $handle = fopen($path, 'r');
                    
                    // Pular a linha de cabeçalho
                    $headers = fgetcsv($handle, 0, $separator);
                    
                    $totalRows = 0;
                    $importedRows = 0;
                    $updatedRows = 0;
                    $ignoredRows = 0;
                    
                    $chunks = collect();
                    
                    while (($data = fgetcsv($handle, 0, $separator)) !== false) {
                        // Skip if the number of columns doesn't match headers
                        if (count($headers) !== count($data)) {
                            $ignoredRows++;
                            continue;
                        }

                        $row = array_combine($headers, $data);
                        $chunks->push($row);
                        
                        $totalRows++;
                        
                        if ($chunks->count() >= $chunkSize) {
                            list($imported, $updated, $ignored) = $this->processChunk($chunks);
                            $importedRows += $imported;
                            $updatedRows += $updated;
                            $ignoredRows += $ignored;
                            $chunks = collect();
                        }
                    }
                    
                    // Processar o último chunk (se houver)
                    if ($chunks->count() > 0) {
                        list($imported, $updated, $ignored) = $this->processChunk($chunks);
                        $importedRows += $imported;
                        $updatedRows += $updated;
                        $ignoredRows += $ignored;
                    }
                    
                    fclose($handle);
                    
                    try {
                        unlink($path);
                    } catch (\Exception $e) {
                        // Ignorar erro
                    }

                    Notification::make()
                        ->success()
                        ->title('Importação concluída')
                        ->icon('heroicon-o-check-circle')
                        ->duration(25000)
                        ->body("Total processado: $totalRows registros. Criados: $importedRows. Atualizados: $updatedRows. Ignorados: $ignoredRows (celular ou data de nascimento vazio ou número de colunas inválido)")
                        ->send();

                    $this->dispatch('refresh');
                }),

                Actions\CreateAction::make()
                    ->label('Adicionar Associado')
                    ->icon('heroicon-o-plus')
                    ->iconPosition(IconPosition::Before),
        ];
    }

    protected function processChunk(Collection $chunks): array
    {
        $imported = 0;
        $updated = 0;
        $ignored = 0;
        
        foreach ($chunks as $row) {
            // Formatar dados
            $celular = preg_replace('/\D/', '', $row['CELULAR Whatsapp'] ?? '');
            
            $dataNascimento = config('app.default_birth_for_empty');
            if (!empty($row['DATA/NASCIMENTO'])) {
                try {
                    $dateFormats = [
                        'd/m/Y',    // 31/12/2023
                        'd/m/y',    // 31/12/23
                        'd-m-Y',    // 31-12-2023
                        'd-m-y',    // 31-12-23
                        'Y-m-d',    // 2023-12-31
                        'y-m-d',    // 23-12-31
                        'd.m.Y',    // 31.12.2023
                        'd.m.y',    // 31.12.23
                        'm/d/Y',    // 12/31/2023
                        'm/d/y',    // 12/31/23
                        'Y/m/d',    // 2023/12/31
                        'y/m/d',    // 23/12/31
                    ];

                    $dateString = trim($row['DATA/NASCIMENTO']);
                    $dataNascimento = null;

                    foreach ($dateFormats as $format) {
                        try {
                            $parsedDate = Carbon::createFromFormat($format, $dateString);
                            
                            // If the date is in the future or less than 15 years ago, subtract 100 years
                            if ($parsedDate->isFuture() || $parsedDate->diffInYears(now()) < 15) {
                                $parsedDate = $parsedDate->subYears(100);
                            }

                            if ($parsedDate && $parsedDate->year > 1900) {
                                $dataNascimento = $parsedDate->format('Y-m-d');
                                break;
                            }
                        } catch (\Exception $e) {
                            continue;
                        }
                    }

                    // If no format matched, try Carbon's parse method as a fallback
                    if (!$dataNascimento) {
                        try {
                            $parsedDate = Carbon::parse($dateString);
                            
                            // If the date is in the future or less than 15 years ago, subtract 100 years
                            if ($parsedDate->isFuture() || $parsedDate->diffInYears(now()) < 15) {
                                $parsedDate = $parsedDate->subYears(100);
                            }
                            
                            if ($parsedDate && $parsedDate->year > 1900) {
                                $dataNascimento = $parsedDate->format('Y-m-d');
                            }
                        } catch (\Exception $e) {
                            Log::warning('Failed to parse date with Carbon::parse: ' . ($dateString ?? 'empty'), [
                                'error' => $e->getMessage(),
                                'row' => $row
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to parse date: ' . ($row['DATA/NASCIMENTO'] ?? 'empty'), [
                        'error' => $e->getMessage(),
                        'row' => $row
                    ]);
                }
            }
            
            if(!empty($celular) && !empty($dataNascimento)) {
                // Verificar se já existe um associado com esse celular e data de nascimento
                $associado = \App\Models\Associado::where('celular', $celular)
                    ->whereDate('data_nascimento', $dataNascimento)
                    ->first();
            
                $data = [
                    'nome' => $row['NOME'] ?? '',
                    'sobrenome' => $row['SOBRENOME'] ?? '',
                    'celular' => $celular,
                    'whatsapp' => $celular,
                    'email' => $row['E-mail'] ?? null,
                    'observacoes' => $row['OBSERVAÇÕES'] ?? null,
                    'frequenta_eventos' => ($row['FREQUENTA OS EVENTOS'] ?? '') === '1',
                    'grupo_whatsapp' => ($row['FAZ PARTE DO GRUPO DO WHATSAPP'] ?? '') === '1',
                    'instagram' => $row['INSTAGRAM'] ?? null,
                    'cartao_beneficios' => ($row['CARTÃO BENEFÍCIOS'] ?? '') === '1',
                    'data_nascimento' => $dataNascimento,
                    'endereco' => $row['ENDEREÇO'] ?? null,
                ];

                if (!empty($row['MEMBRO DESDE (DATA)'])) {
                    try {
                        $data['cartao_beneficios_desde'] = \Carbon\Carbon::createFromFormat('m/Y', $row['MEMBRO DESDE (DATA)'])->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Ignorar erro de formato
                    }
                }
                
                if ($associado) {
                    // Atualizar associado existente
                    $associado->update($data);
                    $updated++;
                } else {
                    // Criar novo associado
                    \App\Models\Associado::create($data);
                    $imported++;
                }
            }
            else
            {
                $ignored++;
            }
        }
        
        return [$imported, $updated, $ignored];
    }
}
