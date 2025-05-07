<?php

namespace App\Filament\Resources\AssociadoResource\Pages;

use App\Filament\Resources\AssociadoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Associado;
use Filament\Notifications\Notification;

class EditAssociado extends EditRecord
{
    protected static string $resource = AssociadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    public function beforeSave(): void
    {
        $data = $this->form->getState();
        
        // Limpa os dados
        if (isset($data['celular'])) {
            $data['celular'] = preg_replace('/[^0-9]/', '', $data['celular']);
            $this->form->fill(['celular' => $data['celular']]);
        }
        
        if (isset($data['email'])) {
            $data['email'] = trim($data['email']);
            $this->form->fill(['email' => $data['email']]);
        }
        
        // Verifica se já existe um associado com este celular (excluindo o registro atual)
        $existeCelular = Associado::query()
            ->where('celular', $data['celular'])
            ->where('id', '!=', $this->record->id)
            ->exists();
            
        if ($existeCelular) {
            Notification::make()
                ->title('Celular já cadastrado')
                ->body('Já existe um associado com este número de celular.')
                ->danger()
                ->send();
                
            $this->halt();
        }
        
        // Verifica se já existe um associado com este email (excluindo o registro atual)
        if (isset($data['email']) && !empty($data['email'])) {
            $existeEmail = Associado::query()
                ->where('email', $data['email'])
                ->where('id', '!=', $this->record->id)
                ->exists();
                
            if ($existeEmail) {
                Notification::make()
                    ->title('Email já cadastrado')
                    ->body('Já existe um associado com este endereço de email.')
                    ->danger()
                    ->send();
                    
                $this->halt();
            }
        }
    }
}
