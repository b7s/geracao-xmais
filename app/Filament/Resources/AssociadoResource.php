<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssociadoResource\Pages;
use App\Filament\Resources\AssociadoResource\RelationManagers;
use App\Filament\Resources\AssociadoResource\Widgets;
use App\Models\Associado;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class AssociadoResource extends Resource
{
    protected static ?string $model = Associado::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $recordTitleAttribute = 'nome_completo';

    protected static ?string $modelLabel = 'Associado';

    protected static ?string $pluralModelLabel = 'Associados';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Pessoais')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('nome')
                                    ->label('Nome')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('sobrenome')
                                    ->label('Sobrenome')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('celular')
                                    ->label('Celular')
                                    ->tel()
                                    ->maxLength(20)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function($state, $old, Set $set, Get $get, Forms\Components\Component $component) {
                                        if (empty($state) || empty($get('data_nascimento'))) {
                                            return;
                                        }
                                        
                                        $record = $component->getRecord();
                                        $recordId = $record ? $record->id : null;
                                        
                                        $query = Associado::query()
                                            ->where('celular', $state)
                                            ->where('data_nascimento', $get('data_nascimento'));
                                        
                                        if ($recordId) {
                                            $query->where('id', '!=', $recordId);
                                        }
                                        
                                        $exists = $query->exists();
                                        
                                        if ($exists) {
                                            $component->addError('Já existe um associado com este celular e data de nascimento.');
                                        }
                                    })
                                    ->columnSpan(1),
                                Forms\Components\DatePicker::make('data_nascimento')
                                    ->label('Data de Nascimento')
                                    ->default('1970-01-01')
                                    ->displayFormat('d/m/Y')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function($state, $old, Set $set, Get $get, Forms\Components\Component $component) {
                                        if (empty($state) || empty($get('celular'))) {
                                            return;
                                        }
                                        
                                        $record = $component->getRecord();
                                        $recordId = $record ? $record->id : null;
                                        
                                        $query = Associado::query()
                                            ->where('celular', $get('celular'))
                                            ->where('data_nascimento', $state);
                                        
                                        if ($recordId) {
                                            $query->where('id', '!=', $recordId);
                                        }
                                        
                                        $exists = $query->exists();
                                        
                                        if ($exists) {
                                            $component->addError('Já existe um associado com este celular e data de nascimento.');
                                        }
                                    })
                                    ->columnSpan(1),
                            ]),
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('whatsapp')
                                    ->label('WhatsApp')
                                    ->tel()
                                    ->mask('(99) 99999-9999')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->label('E-mail')
                                    ->email()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                            ]),
                        Forms\Components\TextInput::make('instagram')
                            ->label('Instagram')
                            ->prefix('@')
                            ->maxLength(255),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Endereço Completo')
                    ->schema([
                        Forms\Components\Textarea::make('endereco')
                            ->hiddenLabel()
                            ->rows(3),
                    ]),

                Forms\Components\Section::make('Atividades')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Toggle::make('cartao_beneficios')
                                    ->label('Cartão Benefícios')
                                    ->default(false)
                                    ->live(),

                                Forms\Components\Toggle::make('frequenta_eventos')
                                    ->label('Frequenta os Eventos')
                                    ->default(false),
                                ]),
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\DatePicker::make('cartao_beneficios_desde')
                                    ->label('Cartão Benefícios Desde')
                                    ->displayFormat('m/Y')
                                    ->format('Y-m')
                                    ->disabled(fn (Get $get) => !$get('cartao_beneficios')),

                                Forms\Components\Toggle::make('grupo_whatsapp')
                                    ->label('Faz parte do Grupo do WhatsApp')
                                    ->default(false),
                            ]),
                    ]),

                Forms\Components\Section::make('Observações')
                    ->schema([
                        Forms\Components\Textarea::make('observacoes')
                            ->hiddenLabel()
                            ->rows(3),
                    ])
                    ->description('Observações sobre o associado. * Não aparece para o associado.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sobrenome')
                    ->label('Sobrenome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('celular')
                    ->label('Celular')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '';
                        // Exibe somente os últimos 4 dígitos, os demais são mascarados com *
                        $length = strlen($state);
                        $maskedPart = str_repeat('*', $length - 4);
                        $lastFourDigits = substr($state, -4);
                        return $maskedPart . $lastFourDigits;
                    }),
                Tables\Columns\TextColumn::make('data_nascimento')
                    ->label('Data de Nascimento')
                    ->date('m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) return '';
                        
                        $parts = explode('@', $state);
                        if (count($parts) != 2) return $state;
                        
                        $localPart = $parts[0];
                        $domain = $parts[1];
                        
                        // Mostrar apenas os 3 primeiros caracteres do email
                        if (strlen($localPart) > 3) {
                            $visiblePart = substr($localPart, 0, 3);
                            $hiddenPart = str_repeat('*', strlen($localPart) - 3);
                            $maskedLocalPart = $visiblePart . $hiddenPart;
                        } else {
                            $maskedLocalPart = $localPart;
                        }
                        
                        return $maskedLocalPart . '@' . $domain;
                    }),
                Tables\Columns\IconColumn::make('frequenta_eventos')
                    ->label('Frequenta Eventos')
                    ->boolean(),
                Tables\Columns\IconColumn::make('grupo_whatsapp')
                    ->label('Grupo WhatsApp')
                    ->boolean(),
                Tables\Columns\IconColumn::make('cartao_beneficios')
                    ->label('Cartão Benefícios')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('frequenta_eventos')
                    ->label('Frequenta Eventos')
                    ->options([
                        '1' => 'Sim',
                        '0' => 'Não',
                    ]),
                Tables\Filters\SelectFilter::make('grupo_whatsapp')
                    ->label('Grupo WhatsApp')
                    ->options([
                        '1' => 'Sim',
                        '0' => 'Não',
                    ]),
                Tables\Filters\SelectFilter::make('cartao_beneficios')
                    ->label('Cartão Benefícios')
                    ->options([
                        '1' => 'Sim',
                        '0' => 'Não',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make('xlsx')
                            ->fromTable()
                            ->withFilename('associados_' . date('Y-m-d'))
                    ]),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssociados::route('/'),
            'create' => Pages\CreateAssociado::route('/create'),
            'edit' => Pages\EditAssociado::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\AssociadosStatsOverview::class,
        ];
    }
}
