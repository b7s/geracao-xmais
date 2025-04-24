<?php

namespace App\Filament\Resources\AssociadoResource\Pages;

use App\Filament\Resources\AssociadoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Associado;
use Filament\Notifications\Notification;

class CreateAssociado extends CreateRecord
{
    protected static string $resource = AssociadoResource::class;

    public function beforeCreate(): void
    {
        $data = $this->form->getState();
        
        // Verifica se já existe um associado com este celular e data de nascimento
        $existe = Associado::where('celular', $data['celular'])
            ->where('data_nascimento', $data['data_nascimento'])
            ->exists();
            
        if ($existe) {
            Notification::make()
                ->title('Associado duplicado')
                ->body('Já existe um associado com este celular e data de nascimento.')
                ->danger()
                ->send();
                
            $this->halt();
        }
    }
}
