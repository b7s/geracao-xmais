<?php

namespace App\Filament\Resources\AssociadoResource\Widgets;

use App\Models\Associado;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AssociadosStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        [$totalAssociados, $associadosComCartao, $associadosNoGrupo, $duplicados] = Cache::remember('stats.associados', 300, function () {
            return [
                Associado::count(),
                Associado::where('cartao_beneficios', true)->count(),
                Associado::where('grupo_whatsapp', true)->count(),
                DB::table('associados')
                ->select('celular', 'data_nascimento', DB::raw('COUNT(*) as total'))
                ->groupBy('celular', 'data_nascimento')
                ->having('total', '>', 1)
                ->count()
            ];
        });

        return [
            Stat::make('Total de Associados', $totalAssociados)
                ->description('Todos os associados cadastrados')
                ->descriptionIcon('heroicon-m-users')
                ->chart([7, 2, 10, 3, 15, 4, $totalAssociados])
                ->color('success'),
            Stat::make('Com Cartão de Benefícios', $associadosComCartao)
                ->description(number_format(($totalAssociados > 0 ? $associadosComCartao / $totalAssociados * 100 : 0), 1) . '% do total')
                ->descriptionIcon('heroicon-m-credit-card')
                ->chart([3, 5, 7, 12, 9, 4, $associadosComCartao])
                ->color('warning'),
            Stat::make('No Grupo do WhatsApp', $associadosNoGrupo)
                ->description(number_format(($totalAssociados > 0 ? $associadosNoGrupo / $totalAssociados * 100 : 0), 1) . '% do total')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->chart([2, 4, 6, 8, 10, 12, $associadosNoGrupo])
                ->color('primary'),
            Stat::make('Possíveis Duplicados', $duplicados)
                ->description('Mesmos celular e data de nascimento')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
