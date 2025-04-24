# Geração XMais - Sistema de Gerenciamento de Associados

<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

## Sobre o Projeto

Geração XMais é um sistema desenvolvido em Laravel com Filament para gerenciar associados, fornecendo uma interface administrativa completa e segura.

### Principais Funcionalidades

- **Gerenciamento de Usuários**: Controle de acesso com usuários administradores
- **Cadastro de Associados**: Formulário completo para cadastro e edição de associados
- **Painel Estatístico**: Visualização rápida de métricas importantes
- **Validação de Associados**: Sistema de validação para evitar duplicidades
- **Cartão de Benefícios**: Controle de associados com cartão de benefícios
- **Grupo WhatsApp**: Gerenciamento de associados no grupo do WhatsApp

## Requisitos do Sistema

- PHP 8.1 ou superior
- Composer
- Node.js e NPM
- Banco de dados compatível com Laravel (MySQL, PostgreSQL, SQLite)

## Instalação

O projeto inclui um comando de instalação personalizado que configura todo o ambiente de desenvolvimento com apenas um comando:

```bash
composer run install
```

Este comando automatiza os seguintes passos:
1. Cria o arquivo .env a partir do exemplo
2. Cria o banco de dados SQLite se não existir
3. Instala as dependências do Composer
4. Executa as migrações do banco de dados
5. Descobre e registra os pacotes
6. Publica os assets do Laravel
7. Cria o link simbólico para a pasta de storage
8. Gera a chave de aplicação
9. Atualiza as dependências do Filament
10. Cria a tabela de notificações
11. Instala as dependências do NPM
12. Compila os assets

## Acessando o Sistema

Após a instalação, você precisará criar um usuário administrador:

```bash
php artisan make:filament-user
```

Após criar o usuário, defina-o como administrador no banco de dados ou via Tinker:

```bash
php artisan tinker --execute="App\Models\User::find(1)->update(['is_admin' => true])"
```

Acesse o sistema pela URL `/admin` e faça login com as credenciais criadas.

## Validação de Associados

O sistema implementa validação para evitar duplicação de associados com base nos seguintes critérios:
- Combinação de celular e data de nascimento deve ser única
- A validação ocorre tanto no formulário do Filament (tempo real) quanto via API

## API Endpoints

O sistema disponibiliza os seguintes endpoints:

- **POST** `/associados/verificar-unicidade` - Verifica se já existe um associado com o mesmo celular e data de nascimento
  - Parâmetros: `celular`, `data_nascimento`, `id` (opcional, para ignorar o próprio registro ao editar)
  - Retorno: `{ "existe": boolean, "mensagem": string }`

## Segurança

O acesso ao painel administrativo é restrito a usuários com a flag `is_admin` ativada. Apenas administradores podem criar novos usuários do sistema.

## Tecnologias Utilizadas

- [Laravel](https://laravel.com)
- [Filament](https://filamentphp.com)
- [Livewire](https://livewire.laravel.com)
- [TailwindCSS](https://tailwindcss.com)
- [Alpine.js](https://alpinejs.dev)

## Licença

Este software é licenciado sob a [Licença MIT](https://opensource.org/licenses/MIT).
