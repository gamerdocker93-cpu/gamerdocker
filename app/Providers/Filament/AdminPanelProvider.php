<?php

namespace App\Providers\Filament;

use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Filament\Admin\Pages\AdvancedPage;
use App\Filament\Admin\Pages\AutoWithdrawPage;
use App\Filament\Admin\Pages\DashboardAdmin;
use App\Filament\Admin\Pages\DigitoPayPaymentPage;
use App\Filament\Admin\Pages\GamesKeyPage;
use App\Filament\Admin\Pages\GatewayPage;
use App\Filament\Admin\Pages\LayoutCssCustom;
use App\Filament\Admin\Pages\SettingMailPage;
use App\Filament\Admin\Pages\SettingSpin;
use App\Filament\Admin\Resources\AffiliateWithdrawResource;
use App\Filament\Admin\Resources\BannerResource;
use App\Filament\Admin\Resources\CategoryResource;
use App\Filament\Admin\Resources\DepositResource;
use App\Filament\Admin\Resources\GameResource;
use App\Filament\Admin\Resources\MissionResource;
use App\Filament\Admin\Resources\OrderResource;
use App\Filament\Admin\Resources\ProviderResource;
use App\Filament\Admin\Resources\ReportResource;
use App\Filament\Admin\Resources\SettingResource;
use App\Filament\Admin\Resources\UserResource;
use App\Filament\Admin\Resources\VipResource;
use App\Filament\Admin\Resources\WalletResource;
use App\Filament\Admin\Resources\WithdrawalResource;
use App\Filament\Resources\GameProviderResource;
use App\Http\Middleware\CheckAdmin;
use App\Livewire\AdminWidgets;
use App\Livewire\WalletOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'danger'  => Color::Red,
                'gray'    => Color::Slate,
                'info'    => Color::Blue,
                'primary' => Color::Indigo,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->font('Roboto Condensed')
            ->brandLogo(fn () => view('filament.components.logo'))

            // Resources antigos
            ->discoverResources(
                in: app_path('Filament/Admin/Resources'),
                for: 'App\\Filament\\Admin\\Resources'
            )

            // NOVOS RESOURCES (fora da pasta Admin)
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )

            ->discoverPages(
                in: app_path('Filament/Admin/Pages'),
                for: 'App\\Filament\\Admin\\Pages'
            )
            ->pages([
                DashboardAdmin::class,
            ])
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->collapsibleNavigationGroups(true)
            ->discoverWidgets(
                in: app_path('Filament/Admin/Widgets'),
                for: 'App\\Filament\\Admin\\Widgets'
            )
            ->widgets([
                WalletOverview::class,
                AdminWidgets::class,
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                // ✅ seguro em produção: não chama ->user() quando não há sessão
                $isAdmin = Auth::check() && Auth::user()?->hasRole('admin');

                return $builder->groups([
                    NavigationGroup::make()
                        ->items([
                            NavigationItem::make('dashboard')
                                ->icon('heroicon-o-home')
                                ->label(fn (): string => __('filament-panels::pages/dashboard.title'))
                                ->url(fn (): string => DashboardAdmin::getUrl())
                                ->isActiveWhen(fn () => request()->routeIs('filament.pages.settings')),
                        ]),

                    $isAdmin
                        ? NavigationGroup::make('Modulos')
                            ->items([
                                ...MissionResource::getNavigationItems(),
                                ...VipResource::getNavigationItems(),
                                ...ReportResource::getNavigationItems(),
                            ])
                        : NavigationGroup::make(),

                    $isAdmin
                        ? NavigationGroup::make('Meus Jogos')
                            ->items([
                                ...CategoryResource::getNavigationItems(),
                                ...ProviderResource::getNavigationItems(),
                                ...GameResource::getNavigationItems(),
                                ...OrderResource::getNavigationItems(),

                                // NOVO MENU PROVIDERS DEFINITIVO
                                ...GameProviderResource::getNavigationItems(),
                            ])
                        : NavigationGroup::make(),

                    $isAdmin
                        ? NavigationGroup::make('Pagamentos')
                            ->items([
                                NavigationItem::make('games-key')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->label('Chaves dos Jogos')
                                    ->url(fn (): string => GamesKeyPage::getUrl())
                                    ->visible(fn (): bool => $isAdmin),

                                NavigationItem::make('gateway')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->label('Gateway de Pagamentos')
                                    ->url(fn (): string => GatewayPage::getUrl())
                                    ->visible(fn (): bool => $isAdmin),

                                NavigationItem::make('auto-withdraw')
                                    ->icon('heroicon-o-arrow-path')
                                    ->label('Auto Saque')
                                    ->url(fn (): string => AutoWithdrawPage::getUrl())
                                    ->visible(fn (): bool => $isAdmin),

                                NavigationItem::make('digitopay-pagamentos')
                                    ->icon('heroicon-o-currency-dollar')
                                    ->label('Histórico de Pagamentos')
                                    ->url(fn (): string => DigitoPayPaymentPage::getUrl())
                                    ->visible(fn (): bool => $isAdmin),
                            ])
                        : NavigationGroup::make(),

                    $isAdmin
                        ? NavigationGroup::make('Afiliados')
                            ->items([
                                NavigationItem::make('withdraw_affiliates')
                                    ->icon('heroicon-o-banknotes')
                                    ->label('Saques de Afiliados')
                                    ->url(fn (): string => AffiliateWithdrawResource::getUrl())
                                    ->visible(fn (): bool => $isAdmin),
                            ])
                        : NavigationGroup::make(),

                    $isAdmin
                        ? NavigationGroup::make('Customização')
                            ->items([
                                ...BannerResource::getNavigationItems(),
                                NavigationItem::make('custom-layout')
                                    ->icon('heroicon-o-paint-brush')
                                    ->label('Customização')
                                    ->url(fn (): string => LayoutCssCustom::getUrl())
                                    ->visible(fn (): bool => $isAdmin),
                            ])
                        : NavigationGroup::make(),

                    $isAdmin
                        ? NavigationGroup::make('Definições')
                            ->items([
                                NavigationItem::make('settings')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->label('Configurações')
                                    ->url(fn (): string => SettingResource::getUrl())
                                    ->visible(fn (): bool => $isAdmin),

                                NavigationItem::make('setting-spin')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->label('Definições do Spin')
                                    ->url(fn (): string => SettingSpin::getUrl())
                                    ->visible(fn (): bool => $isAdmin),

                                NavigationItem::make('setting-mail')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->label('Definições de Email')
                                    ->url(fn (): string => SettingMailPage::getUrl())
                                    ->visible(fn (): bool => $isAdmin),
                            ])
                        : NavigationGroup::make(),

                    $isAdmin
                        ? NavigationGroup::make('Usuários')
                            ->items([
                                ...UserResource::getNavigationItems(),
                                ...WalletResource::getNavigationItems(),
                                ...DepositResource::getNavigationItems(),
                                ...WithdrawalResource::getNavigationItems(),
                            ])
                        : NavigationGroup::make(),

                    NavigationGroup::make('Manutenção')
                        ->items([
                            NavigationItem::make('advanced_page')
                                ->icon('heroicon-o-wrench')
                                ->label('Opções Avançada')
                                ->url(fn (): string => AdvancedPage::getUrl())
                                ->visible(fn (): bool => $isAdmin),

                            NavigationItem::make('Limpar o cache')
                                ->url(url('/clear'))
                                ->icon('heroicon-o-trash'),
                        ]),
                ]);
            })
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
                CheckAdmin::class,
            ])
            ->plugin(FilamentSpatieRolesPermissionsPlugin::make());
    }
}