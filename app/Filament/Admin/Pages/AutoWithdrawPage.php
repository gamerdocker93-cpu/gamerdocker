<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;

class AutoWithdrawPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Auto Saque';
    protected static ?string $title = 'Auto Saque';
    protected static ?string $slug = 'auto-saque';
    protected static string $view = 'filament.admin.pages.auto-withdraw-page';

    public ?array $data = [];
    public Setting $setting;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function mount(): void
    {
        $this->setting = Setting::firstOrCreate([]);

        // garante que o valor do gateway sempre seja válido pro Select
        $gateway = (string) ($this->setting->auto_withdraw_gateway ?? 'auto');
        $allowed = ['auto', 'sharkpay', 'digitopay'];
        if (!in_array($gateway, $allowed, true)) {
            $gateway = 'auto';
        }

        $this->form->fill([
            'auto_withdraw_enabled' => (bool) ($this->setting->auto_withdraw_enabled ?? false),
            'auto_withdraw_players' => (bool) ($this->setting->auto_withdraw_players ?? false),

            // deixamos “visível”, mas você pode manter desligado por enquanto
            'auto_withdraw_affiliates' => (bool) ($this->setting->auto_withdraw_affiliates ?? false),
            'auto_withdraw_affiliate_enabled' => (bool) ($this->setting->auto_withdraw_affiliate_enabled ?? false),

            'auto_withdraw_gateway' => $gateway,
            'auto_withdraw_batch_size' => (int) ($this->setting->auto_withdraw_batch_size ?? 20),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Auto Saque')
                    ->description('Ative/desative o saque automático e escolha o gateway de payout.')
                    ->schema([
                        Toggle::make('auto_withdraw_enabled')
                            ->label('Auto Saque (Master)')
                            ->helperText('Liga/desliga a automação de saque no sistema inteiro.')
                            ->inline(false),

                        Toggle::make('auto_withdraw_players')
                            ->label('Players (Usuários)')
                            ->helperText('Quando ativo, processa saques de jogadores automaticamente.')
                            ->inline(false),

                        Toggle::make('auto_withdraw_affiliates')
                            ->label('Afiliados (toggle interno)')
                            ->helperText('Vamos ligar depois, em outra etapa.')
                            ->inline(false),

                        Toggle::make('auto_withdraw_affiliate_enabled')
                            ->label('Afiliados (master)')
                            ->helperText('Vamos ligar depois, em outra etapa.')
                            ->inline(false),

                        Select::make('auto_withdraw_gateway')
                            ->label('Gateway para pagamento automático')
                            ->options([
                                'auto' => 'Auto (padrão do sistema)',
                                'sharkpay' => 'SharkPay',
                                'digitopay' => 'DigitoPay (a implementar)',
                            ])
                            ->native(false)
                            ->required(),

                        TextInput::make('auto_withdraw_batch_size')
                            ->label('Batch por execução')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(200)
                            ->helperText('Quantos saques processar por rodada do job.')
                            ->required(),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvar')
                ->action(fn () => $this->submit()),
        ];
    }

    public function submit(): void
    {
        try {
            $setting = Setting::firstOrCreate([]);

            // pega o state real do form (mais confiável)
            $state = $this->form->getState();

            $gateway = (string) ($state['auto_withdraw_gateway'] ?? 'auto');
            $allowed = ['auto', 'sharkpay', 'digitopay'];
            if (!in_array($gateway, $allowed, true)) {
                $gateway = 'auto';
            }

            $setting->update([
                'auto_withdraw_enabled' => (int) (!empty($state['auto_withdraw_enabled'])),
                'auto_withdraw_players' => (int) (!empty($state['auto_withdraw_players'])),

                'auto_withdraw_affiliates' => (int) (!empty($state['auto_withdraw_affiliates'])),
                'auto_withdraw_affiliate_enabled' => (int) (!empty($state['auto_withdraw_affiliate_enabled'])),

                'auto_withdraw_gateway' => $gateway,
                'auto_withdraw_batch_size' => (int) ($state['auto_withdraw_batch_size'] ?? 20),
            ]);

            $this->setting = $setting->fresh();

            Notification::make()
                ->title('Salvo')
                ->body('Configuração de Auto Saque atualizada com sucesso.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Log::error('AutoWithdrawPage submit error', [
                'message' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Erro ao salvar')
                ->body('Falha ao salvar. Veja os logs para detalhes.')
                ->danger()
                ->send();

            // rethrow opcional (eu NÃO rethrow pra não virar 500)
            // throw $e;
        }
    }
}