<?php

namespace App\Filament\Associado\Pages;

use App\Models\Associado;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditProfilePage extends Page
{
    use InteractsWithForms;
    use InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $title = 'Editar Perfil';

    protected static string $view = 'filament.associado.pages.edit-profile';
    
    protected static ?string $slug = 'edit-profile';

    protected static ?string $navigationLabel = 'Editar Perfil';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $auth = auth('associado')->user();
        $this->form->fill([
            'nome' => $auth->nome,
            'sobrenome' => $auth->sobrenome,
            'celular' => $auth->celular,
            'data_nascimento' => $auth->data_nascimento,
            'whatsapp' => $auth->whatsapp,
            'email' => $auth->email,
            'instagram' => $auth->instagram,
            'endereco' => $auth->endereco,
            'cartao_beneficios' => $auth->cartao_beneficios,
            'cartao_beneficios_desde' => $auth->cartao_beneficios_desde,
            'frequenta_eventos' => $auth->frequenta_eventos,
            'grupo_whatsapp' => $auth->grupo_whatsapp,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informações Pessoais')
                    ->schema([
                        Components\Grid::make()
                            ->schema([
                                TextInput::make('nome')
                                    ->label('Nome')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('sobrenome')
                                    ->label('Sobrenome')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Components\Grid::make()
                            ->schema([
                                TextInput::make('celular')
                                    ->label('Celular')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function($state, $old, Set $set, Get $get, Components\Component $component) {
                                        if (empty($state) || empty($get('data_nascimento'))) {
                                            return;
                                        }
                                        
                                        $userId = auth('associado')->id();
                                        
                                        $query = Associado::query()
                                            ->where('celular', $state)
                                            ->where('data_nascimento', $get('data_nascimento'))
                                            ->where('id', '!=', $userId);
                                        
                                        $exists = $query->exists();
                                        
                                        if ($exists) {
                                            $component->addError('Já existe um associado com este celular e data de nascimento.');
                                        }
                                    }),
                                DatePicker::make('data_nascimento')
                                    ->label('Data de Nascimento')
                                    ->required()
                                    ->displayFormat('d/m/Y')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function($state, $old, Set $set, Get $get, Components\Component $component) {
                                        if (empty($state) || empty($get('celular'))) {
                                            return;
                                        }
                                        
                                        $userId = auth('associado')->id();
                                        
                                        $query = Associado::query()
                                            ->where('celular', $get('celular'))
                                            ->where('data_nascimento', $state)
                                            ->where('id', '!=', $userId);
                                        
                                        $exists = $query->exists();
                                        
                                        if ($exists) {
                                            $component->addError('Já existe um associado com este celular e data de nascimento.');
                                        }
                                    }),
                            ]),
                        Components\Grid::make()
                            ->schema([
                                TextInput::make('whatsapp')
                                    ->label('WhatsApp')
                                    ->tel()
                                    ->mask('(99) 99999-9999')
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('E-mail')
                                    ->email()
                                    ->maxLength(255)
                                    ->unique(table: 'associados', column: 'email', ignorable: auth('associado')->user()),
                            ]),
                        TextInput::make('instagram')
                            ->label('Instagram')
                            ->prefix('@')
                            ->maxLength(255),
                    ])
                    ->columns(1),

                Section::make('Atividades')
                    ->schema([
                        Components\Grid::make()
                            ->schema([
                                Toggle::make('cartao_beneficios')
                                    ->label('Cartão Benefícios')
                                    ->default(false)
                                    ->live(),

                                Toggle::make('frequenta_eventos')
                                    ->label('Frequenta os Eventos')
                                    ->default(false),
                            ]),
                        Components\Grid::make()
                            ->schema([
                                DatePicker::make('cartao_beneficios_desde')
                                    ->label('Cartão Benefícios Desde')
                                    ->displayFormat('m/Y')
                                    ->format('Y-m')
                                    ->disabled(fn (Get $get) => !$get('cartao_beneficios')),

                                Toggle::make('grupo_whatsapp')
                                    ->label('Faz parte do Grupo do WhatsApp')
                                    ->default(false),
                            ]),
                    ]),

                Section::make('Endereço')
                    ->schema([
                        Textarea::make('endereco')
                            ->label('Endereço Completo')
                            ->rows(3),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvar')
                ->submit('save'),
            
            Action::make('cancel')
                ->label('Cancelar')
                ->url(route('filament.associado.pages.profile-page'))
                ->color('gray'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $associado = Associado::find(auth('associado')->id());
        $associado->fill($data);
        $associado->save();
        
        Notification::make()
            ->title('Dados atualizados com sucesso!')
            ->success()
            ->send();
            
        $this->redirect(route('filament.associado.pages.edit-profile'));
    }
} 