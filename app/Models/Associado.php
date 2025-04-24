<?php

namespace App\Models;

use Carbon\Carbon;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Evento;

class Associado extends Authenticatable implements HasName
{
    use HasFactory, Notifiable;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'sobrenome',
        'celular',
        'whatsapp',
        'email',
        'observacoes',
        'frequenta_eventos',
        'grupo_whatsapp',
        'instagram',
        'cartao_beneficios',
        'cartao_beneficios_desde',
        'data_nascimento',
        'endereco',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_nascimento' => 'date',
        'frequenta_eventos' => 'boolean',
        'grupo_whatsapp' => 'boolean',
        'cartao_beneficios' => 'boolean',
        'cartao_beneficios_desde' => 'date',
    ];

    /**
     * Obtenha o nome completo do associado.
     */
    public function getNomeCompletoAttribute(): string
    {
        return "{$this->nome} {$this->sobrenome}";
    }

    /**
     * Verificar autenticação por telefone e data de nascimento
     */
    public static function autenticarPorTelefoneEDataNascimento(string $telefone, string $data_nascimento): ?self
    {
        $data = Carbon::createFromFormat('d/m/Y', $data_nascimento)->startOfDay();

        return self::where('celular', $telefone)
            ->whereDate('data_nascimento', $data)
            ->first();
    }

    /**
     * Get the name for display in Filament.
     */
    public function getFilamentName(): string
    {
        return $this->nome_completo ?? "{$this->nome} {$this->sobrenome}";
    }
}
