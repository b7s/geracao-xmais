<?php

namespace App\Filament\Associado\Pages;

use App\Models\Associado;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.login-associado';
    
    protected static bool $shouldRegisterNavigation = false;
    
    /**
     * Maximum login attempts allowed.
     */
    public int $maxAttempts = 3;

    /**
     * Minutes to lockout login attempts.
     */
    public int $decayMinutes = 1;
    
    public function hasLogo(): bool
    {
        return false; // Não usaremos o logo padrão, já que definimos nosso próprio no template
    }
    
    public function getHeading(): string
    {
        return '';
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
                    ->helperText('Digite apenas números. Ex: 81999998888'),
                
                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->placeholder('Digite seu email')
                    ->email()
                    ->autocomplete(),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'celular' => $data['celular'],
            'email' => $data['email'],
        ];
    }

    /**
     * Get the throttle key for the given request.
     */
    protected function throttleKey(array $data): string
    {
        return strtolower($data['celular'] . '|' . $data['email'] . '|' . request()->ip());
    }

    /**
     * Check if the user has too many failed login attempts.
     */
    protected function hasTooManyLoginAttempts(array $data): bool
    {
        return RateLimiter::tooManyAttempts(
            $this->throttleKey($data),
            $this->maxAttempts
        );
    }

    /**
     * Increment the login attempts for the user.
     */
    protected function incrementLoginAttempts(array $data): void
    {
        RateLimiter::hit(
            $this->throttleKey($data),
            $this->decayMinutes * 60
        );
    }

    /**
     * Get the number of seconds until the next login attempt is available.
     */
    protected function availableIn(array $data): int
    {
        return RateLimiter::availableIn(
            $this->throttleKey($data)
        );
    }

    public function authenticate(): LoginResponse
    {
        $data = $this->form->getState();
        
        // Check for too many login attempts
        if ($this->hasTooManyLoginAttempts($data)) {
            $seconds = $this->availableIn($data);
            
            throw ValidationException::withMessages([
                'data.celular' => __('Muitas tentativas de login. Por favor, tente novamente em :seconds segundos.', ['seconds' => ceil($seconds / 60)]),
            ]);
        }
        
        // Limpa os dados de entrada
        $celular = preg_replace('/[^0-9]/', '', $data['celular']);
        $email = trim($data['email']);

        $associado = Associado::query()
            ->where([
                'celular' => $celular,
                'email' => $email,
            ])
            ->first();

        if (!$associado) {
            // Increment login attempts
            $this->incrementLoginAttempts($data);
            
            throw ValidationException::withMessages([
                'data.celular' => 'As credenciais fornecidas não correspondem aos nossos registros.',
            ]);
        }

        // Login successful, clear the rate limiter
        RateLimiter::clear($this->throttleKey($data));

        Auth::guard('associado')->login($associado);
        
        session()->regenerate();
        
        return app(LoginResponse::class);
    }
}
