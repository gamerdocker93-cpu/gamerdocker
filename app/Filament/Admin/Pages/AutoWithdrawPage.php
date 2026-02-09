<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;

class AutoWithdrawPage extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Saque Automático';

    protected static ?string $navigationGroup = 'Pagamentos';

    protected static string $view = 'filament.admin.pages.auto-withdraw-page';

    public ?array $data = [];

    public Setting $setting;

    /**
     * Permissão (somente admin)
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * Load settings
     */
    public function mount(): void
    {
        $setting = Setting::first();

        if (!$setting) {
            $setting = Setting::create([]);
        }

        $this->setting = $setting;

        $this->form->fill($setting->toArray());
    }

    /**
     * Formulário
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Configuração de Saque Automático')
                    ->description('Controle de pagamentos automáticos do sistema')
                    ->schema([

                        Toggle::make('auto_withdraw_players')
                            ->label('Saque automático para jogadores')
                            ->helperText('Libera pagamentos automáticos para usuários que ganham no site'),

                        Toggle::make('auto_withdraw_affiliates')
                            ->label('Saque automático para afiliados')
                            ->helperText('Libera pagamentos automáticos para afiliados'),

                        Select::make('auto_withdraw_gateway')
                            ->label('Gateway principal')
                            ->options([
                                'auto'      => 'Automático',
                                'sharkpay'  => 'SharkPay',
                                'digitopay' => 'DigitoPay',
                                'stripe'    => 'Stripe',
                            ])
                            ->default('auto')
                            ->helperText('Gateway usado para pagamentos automáticos'),

                    ])
                    ->columns(1),

            ])
            ->statePath('data');
    }

    /**
     * Salvar
     */
    public function submit(): void
    {
        try {

            if (env('APP_DEMO')) {
                Notification::make()
                    ->title('Modo Demo')
                    ->body('Alterações não permitidas em modo demonstração.')
                    ->danger()
                    ->send();

                return;
            }

            $setting = Setting::first();

            if ($setting) {
                $setting->update($this->data);
            } else {
                Setting::create($this->data);
            }

            Notification::make()
                ->title('Configuração salva')
                ->body('Saque automático atualizado com sucesso.')
                ->success()
                ->send();

        } catch (Halt $exception) {

            Notification::make()
                ->title('Erro')
                ->body('Erro ao salvar configurações.')
                ->danger()
                ->send();

        }
    }
}
