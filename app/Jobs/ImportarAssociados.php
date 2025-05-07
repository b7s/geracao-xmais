<?php

namespace App\Jobs;

use App\Models\Associado;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportarAssociados implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filepath;
    protected $importSessionId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filepath, string $importSessionId)
    {
        $this->filepath = $filepath;
        $this->importSessionId = $importSessionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Abre o arquivo
            $handle = fopen($this->filepath, 'r');
            
            // Lê o cabeçalho
            $headers = fgetcsv($handle, 0, ';');
            
            // Configuração para processamento em chunks
            $chunkSize = 100;
            $rows = [];
            
            // Lê todas as linhas para o array
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
            
            $totalRows = count($rows);
            $count = 0;
            $errors = [];
            
            // Processa em chunks
            $chunks = array_chunk($rows, $chunkSize);
            
            foreach ($chunks as $chunkIndex => $chunk) {
                foreach ($chunk as $row) {
                    try {
                        $data = [];
                        
                        // Mapeia as colunas CSV para campos do modelo
                        foreach ($headers as $index => $header) {
                            if (isset($row[$index])) {
                                $value = trim($row[$index]);
                                
                                // Converte valores booleanos
                                if (in_array($header, ['FREQUENTA OS EVENTOS', 'FAZ PARTE DO GRUPO DO WHATSAPP', 'CARTÃO BENEFÍCIOS'])) {
                                    $value = $value === '1';
                                }
                                
                                // Converte datas
                                if ($header === 'DATA/NASCIMENTO' && !empty($value)) {
                                    try {
                                        $value = Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
                                    } catch (\Exception $e) {
                                        $value = null;
                                    }
                                }
                                
                                if ($header === 'DESDE (MÊS/ANO)' && !empty($value)) {
                                    try {
                                        $value = Carbon::createFromFormat('m/Y', $value)->format('Y-m-d');
                                    } catch (\Exception $e) {
                                        $value = null;
                                    }
                                }
                                
                                if ($header === 'MEMBRO DESDE (DATA)' && !empty($value)) {
                                    try {
                                        $value = Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
                                    } catch (\Exception $e) {
                                        $value = null;
                                    }
                                }
                                
                                // Mapeamento de cabeçalhos para campos do modelo
                                $fieldMap = [
                                    'Nº' => 'id',
                                    'NOME' => 'nome',
                                    'SOBRENOME' => 'sobrenome',
                                    'CELULAR Whatsapp' => 'celular',
                                    'E-mail' => 'email',
                                    'OBSERVAÇÕES' => 'observacoes',
                                    'FREQUENTA OS EVENTOS' => 'frequenta_eventos',
                                    'FAZ PARTE DO GRUPO DO WHATSAPP' => 'grupo_whatsapp',
                                    'INSTAGRAM' => 'instagram',
                                    'CARTÃO BENEFÍCIOS' => 'cartao_beneficios',
                                    'DESDE (MÊS/ANO)' => 'cartao_beneficios_desde',
                                    'DATA/NASCIMENTO' => 'data_nascimento',
                                    'MEMBRO DESDE (DATA)' => 'membro_desde',
                                    // Removendo 'ENDEREÇO' do mapeamento para evitar o erro
                                ];

                                $field = $fieldMap[$header] ?? strtolower(str_replace(' ', '_', $header));

                                // Limpa o celular (remove tudo exceto números)
                                if ($field === 'celular' && !empty($value)) {
                                    $value = preg_replace('/[^0-9]/', '', $value);
                                }

                                // Limpa o email (apenas trim já que o trim inicial pode não ser suficiente)
                                if ($field === 'email' && !empty($value)) {
                                    $value = trim($value);
                                }

                                $data[$field] = $value;
                            }
                        }
                        
                        // Cria o registro
                        $associado = new Associado();
                        $associado->fill($data);
                        $associado->save();
                        
                        $count++;
                    } catch (\Exception $e) {
                        $errors[] = 'Linha ' . ($count + 2) . ': ' . $e->getMessage();
                    }
                }
                
                // Atualiza notificação de progresso a cada chunk
                $processed = min(($chunkIndex + 1) * $chunkSize, $totalRows);
                $percentage = round(($processed / $totalRows) * 100);
                
                Notification::make($this->importSessionId . '_progress')
                    ->title('Importação em andamento')
                    ->body("Processando: {$processed} de {$totalRows} registros ({$percentage}%)")
                    ->info()
                    ->persistent()
                    ->send();
                
                // Limpa o cache para liberar memória
                gc_collect_cycles();
            }
            
            // Envia notificação final após conclusão
            if (count($errors) > 0) {
                // Limita o número de erros mostrados no log
                $errorsToLog = array_slice($errors, 0, 50);
                if (count($errors) > 50) {
                    $errorsToLog[] = '... e mais ' . (count($errors) - 50) . ' erros.';
                }
                
                Log::error('Erros na importação:', $errorsToLog);
                
                Notification::make($this->importSessionId . '_complete')
                    ->title('Importação concluída com erros')
                    ->body("Foram importados {$count} associados com " . count($errors) . " erros.")
                    ->danger()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('reload')
                            ->label('Recarregar página')
                            ->button()
                            ->url(request()->header('Referer'))
                            ->openUrlInNewTab(false)
                    ])
                    ->persistent()
                    ->send();
            } else {
                Notification::make($this->importSessionId . '_complete')
                    ->title('Importação concluída')
                    ->body("{$count} associados foram importados com sucesso.")
                    ->success()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('reload')
                            ->label('Recarregar página')
                            ->button()
                            ->url(request()->header('Referer'))
                            ->openUrlInNewTab(false)
                    ])
                    ->persistent()
                    ->send();
            }
            
        } catch (\Throwable $e) {
            Log::error('Erro fatal na importação: ' . $e->getMessage());
            
            Notification::make($this->importSessionId . '_error')
                ->title('Erro na importação')
                ->body('Ocorreu um erro durante o processamento: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }
} 