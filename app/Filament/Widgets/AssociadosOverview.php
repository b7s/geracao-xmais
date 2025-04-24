<?php

namespace App\Filament\Widgets;

use App\Models\Associado;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssociadosOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total de Associados', Associado::count())
                ->description('Número total de associados cadastrados')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),
            
            Stat::make('Frequentam Eventos', Associado::where('frequenta_eventos', true)->count())
                ->description('Associados que frequentam eventos')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),
            
            Stat::make('Grupo WhatsApp', Associado::where('grupo_whatsapp', true)->count())
                ->description('Associados no grupo de WhatsApp')
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color('warning'),
            
            Stat::make('Com Cartão Benefícios', Associado::where('cartao_beneficios', true)->count())
                ->description('Associados com cartão de benefícios')
                ->descriptionIcon('heroicon-o-credit-card')
                ->color('info'),
        ];
    }
} 