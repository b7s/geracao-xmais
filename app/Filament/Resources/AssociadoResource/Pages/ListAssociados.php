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
                            \pxlrbt\FilamentExcel\Columns\Column::make('whatsapp')
                                ->heading('WhatsApp'),
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
                    \Filament\Forms\Components\Section::make('Modelo de Importação')
                        ->description('Baixe o modelo CSV para preencher com os dados dos associados.')
                        ->schema([
                            \Filament\Forms\Components\View::make('filament.components.download-model-button')
                                ->viewData([
                                    'url' => asset('modelos/modelo-importacao-associados.csv'),
                                    'label' => 'Baixar modelo CSV',
                                ]),
                        ])
                        ->collapsed()
                        ->collapsible(),
                ])
                ->action(function (array $data) {
                    $file = $data['csvFile'];
                    $path = storage_path('app/public/' . $file);
                    
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
                    $headers = fgetcsv($handle, 0, ';');
                    
                    $totalRows = 0;
                    $importedRows = 0;
                    $updatedRows = 0;
                    $ignoredRows = 0;
                    
                    $chunks = collect();
                    
                    while (($data = fgetcsv($handle, 0, ';')) !== false) {
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
                    
                    Notification::make()
                        ->success()
                        ->title('Importação concluída')
                        ->icon('heroicon-o-check-circle')
                        ->duration(25000)
                        ->body("Total processado: $totalRows registros. Criados: $importedRows. Atualizados: $updatedRows. Ignorados (tel ou data de nascimento vazio): $ignoredRows.")
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
            
            $dataNascimento = null;
            if (!empty($row['DATA/NASCIMENTO'])) {
                try {
                    $dataNascimento = \Carbon\Carbon::createFromFormat('d/m/Y', $row['DATA/NASCIMENTO'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // Ignorar erro de formato - deixar como null
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
                    'whatsapp' => $row['Whatsapp'] ?? null,
                    'email' => $row['E-mail'] ?? null,
                    'observacoes' => $row['OBSERVAÇÕES'] ?? null,
                    'frequenta_eventos' => ($row['FREQUENTA OS EVENTOS'] ?? '') === '1',
                    'grupo_whatsapp' => ($row['FAZ PARTE DO GRUPO DO WHATSAPP'] ?? '') === '1',
                    'instagram' => $row['INSTAGRAM'] ?? null,
                    'cartao_beneficios' => ($row['CARTÃO BENEFÍCIOS'] ?? '') === '1',
                    'data_nascimento' => $dataNascimento,
                    'endereco' => $row['ENDEREÇO'] ?? null,
                ];

                if (!empty($row['DESDE (MÊS/ANO)'])) {
                    try {
                        $data['cartao_beneficios_desde'] = \Carbon\Carbon::createFromFormat('m/Y', $row['DESDE (MÊS/ANO)'])->format('Y-m-d');
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
