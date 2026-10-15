<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->brandName('Cardyshub')
            ->brandLogo(asset('brand/full-colored.svg'))
            ->favicon(asset('brand/favicon-32.png'))
            ->colors([
                'primary' => Color::hex('#FA0467'),
            ])
            // A branded sign-in screen at /admin/login, picking up the brand
            // name, logo, favicon and primary colour set above. Without it
            // Filament bounces staff to the storefront's customer login, which
            // is a jarring hand-off from an admin URL.
            //
            // Safe to expose: Filament authenticates with attemptWhen(...
            // canAccessPanel), and User::canAccessPanel() returns is_admin, so
            // a customer entering correct credentials here is refused a session
            // rather than let in and shown a 403.
            ->login()
            // Adds "Profile" to the user menu: name, email and password, all
            // editable from inside the panel. Without it there is no way to
            // change the admin password on a server with no shell, short of
            // hand-writing a bcrypt hash into the database.
            //
            // isSimple: false renders it inside the panel shell rather than as
            // a standalone page, so you keep the sidebar and can get back out.
            //
            // Filament's own page already asks for the current password before
            // it will change the password or the email, so an unattended
            // session cannot be used to lock the real owner out.
            ->profile(isSimple: false)
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            // FilamentInfoWidget is gone: it advertises the framework version
            // and links to Filament's docs, which is of no use to whoever is
            // running the shop and was taking up half the dashboard.
            ->widgets([
                AccountWidget::class,
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
            ]);
    }
}
