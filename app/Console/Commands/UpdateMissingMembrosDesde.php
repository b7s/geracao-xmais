<?php

namespace App\Console\Commands;

use App\Models\Associado;
use Illuminate\Console\Command;

class UpdateMissingMembrosDesde extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'associados:update-membros-desde';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza a coluna membro_desde para registros existentes usando a data de created_at';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando atualização de membro_desde para registros existentes...');
        
        $total = Associado::whereNull('membro_desde')->count();
        $this->info("Encontrados {$total} registros com membro_desde nulo.");
        
        if ($total === 0) {
            $this->info('Nenhum registro para atualizar.');
            return self::SUCCESS;
        }
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        Associado::whereNull('membro_desde')->chunkById(100, function ($associados) use ($bar) {
            foreach ($associados as $associado) {
                $associado->membro_desde = $associado->created_at;
                $associado->save();
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine(2);
        $this->info('Atualização concluída com sucesso!');
        
        return self::SUCCESS;
    }
}
