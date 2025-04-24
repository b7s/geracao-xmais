<?php

namespace App\Providers\Filament;

use App\Filament\Associado\Pages\Login;
use App\Filament\Associado\Pages\PerfilAssociado;
use App\Filament\Associado\Pages\EditProfilePage;
use App\Models\Associado;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AssociadoPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('associado')
            ->path('associado')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Associado/Resources'), for: 'App\\Filament\\Associado\\Resources')
            ->discoverPages(in: app_path('Filament/Associado/Pages'), for: 'App\\Filament\\Associado\\Pages')
            ->pages([
                PerfilAssociado::class,
                EditProfilePage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Associado/Widgets'), for: 'App\\Filament\\Associado\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('associado')
            ->tenant(null)
            ->profile(null)
            ->registration(null)
            ->emailVerification(null)
            ->passwordReset(null)
            ->databaseNotifications()
            ->brandName('Geração xmais - Associado')
            ->brandLogo(asset('images/logo-geracao-xmais.png'))
            ->favicon(asset('images/favicon.png'));
    }
}
