<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AutoWithdrawPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static string $view = 'filament.admin.pages.auto-withdraw-page';

    public bool $enabled = false;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function mount(): void
    {
        $setting = Setting::first();
        $this->enabled = (bool) ($setting?->auto_withdraw_enabled ?? false);
    }

    public function save(): void
    {
        if (env('APP_DEMO')) {
            Notification::make()
                ->title('Atenção')
                ->body('Você não pode realizar esta alteração na versão demo')
                ->danger()
                ->send();
            return;
        }

        $setting = Setting::first();
        if (!$setting) {
            $setting = Setting::create([
                'auto_withdraw_enabled' => $this->enabled ? 1 : 0,
            ]);
        } else {
            $setting->update([
                'auto_withdraw_enabled' => $this->enabled ? 1 : 0,
            ]);
        }

        Notification::make()
            ->title('Salvo')
            ->body('Configuração de Auto Saque atualizada com sucesso.')
            ->success()
            ->send();
    }
}