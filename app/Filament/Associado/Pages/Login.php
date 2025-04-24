<?php

namespace App\Filament\Associado\Pages;

use App\Models\Associado;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.login-associado';
    
    protected static bool $shouldRegisterNavigation = false;
    
    public function hasLogo(): bool
    {
        return false; // Não usaremos o logo padrão, já que definimos nosso próprio no template
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('celular')
                    ->label('Celular')
                    ->required()
                    ->placeholder('Digite seu celular')
                    ->tel()
                    ->helperText('Digite apenas números. Ex: 11999998888'),
                
                DatePicker::make('data_nascimento')
                    ->label('Data de Nascimento')
                    ->required()
                    ->placeholder('Selecione sua data de nascimento')
                    ->displayFormat('d/m/Y')
                    ->native(true),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'celular' => $data['celular'],
            'data_nascimento' => $data['data_nascimento'],
        ];
    }

    public function authenticate(): LoginResponse
    {
        $data = $this->form->getState();
        
        $celular = $data['celular'];
        $dataNascimento = $data['data_nascimento'];

        $dataNascimentoFormatada = Carbon::parse($dataNascimento)->startOfDay();

        $associado = Associado::query()
            ->where('celular', $celular)
            ->whereDate('data_nascimento', $dataNascimentoFormatada)
            ->first();

        if (!$associado) {
            throw ValidationException::withMessages([
                'data.celular' => 'As credenciais fornecidas não correspondem aos nossos registros.',
            ]);
        }

        Auth::guard('associado')->login($associado);
        
        session()->regenerate();
        
        return app(LoginResponse::class);
    }
}
