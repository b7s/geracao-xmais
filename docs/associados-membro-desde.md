# Implementação do Campo "Membro Desde"

Este documento descreve a implementação do campo `membro_desde` para associados na aplicação.

## Visão Geral

O campo `membro_desde` foi adicionado para registrar a data em que uma pessoa se tornou associada da organização. Esta data pode diferir da data de criação do registro no sistema (`created_at`).

## Detalhes Técnicos

### Banco de Dados

- Adicionada a coluna `membro_desde` (tipo `date`, nullable) à tabela `associados`
- A coluna está localizada após `endereco`
- Migration: `2025_05_07_011237_add_membro_desde_to_associados_table.php`

### Model

O campo foi adicionado ao modelo `Associado`:

- Incluído na propriedade `$fillable`
- Definido o cast para `date`

```php
protected $fillable = [
    // ... outros campos ...
    'membro_desde',
];

protected $casts = [
    // ... outros casts ...
    'membro_desde' => 'date',
];
```

### Formulário Filament

Adicionado na seção "Atividades" do formulário de associados (AssociadoResource):

```php
Forms\Components\DatePicker::make('membro_desde')
    ->label('Membro Desde')
    ->displayFormat('d/m/Y')
    ->default(now())
```

### Importação de Dados

- Adicionado o suporte no `ImportarAssociados.php` para o campo `MEMBRO DESDE (DATA)`
- Formato esperado: `dd/mm/yyyy`

### Migração de Dados Existentes

Foi criado um comando para preencher o campo `membro_desde` para registros existentes:

```bash
php artisan associados:update-membros-desde
```

Este comando copia a data de `created_at` para `membro_desde` em todos os registros onde `membro_desde` é nulo.

### Views

A view de perfil do associado foi atualizada para mostrar a data de `membro_desde`, com fallback para `created_at`:

```php
{{ $auth->membro_desde ? $auth->membro_desde->format('d/m/Y') : $auth->created_at->format('d/m/Y') }}
```

## Fluxo de Dados

```
[Formulário de Cadastro] -> [Model Associado] -> [Banco de Dados]
[Importação CSV] -> [Model Associado] -> [Banco de Dados]
[Banco de Dados] -> [Model Associado] -> [View de Perfil]
``` 