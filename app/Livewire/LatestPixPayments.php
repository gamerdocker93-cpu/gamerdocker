<?php

namespace App\Livewire;

use App\Models\DigitoPayPayment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPixPayments extends BaseWidget
{
    protected static ?string $heading = 'Pagamentos Realizados';

    protected static ?int $navigationSort = -1;

    protected int | string | array $columnSpan = 'full';

    /**
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(DigitoPayPayment::query())
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->columns([

                Tables\Columns\TextColumn::make('payment_id')
                    ->label('Pagamento ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('pix_key')
                    ->label('Chave Pix')
                    ->searchable(),

                Tables\Columns\TextColumn::make('pix_type')
                    ->label('Tipo de Chave')
                    ->badge(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('BRL')
                    ->label('Valor'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {

                        'pendente', 'pending' => 'warning',

                        'pago', 'paid', 'success' => 'success',

                        'cancelado', 'canceled', 'failed' => 'danger',

                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i'),

            ]);
    }

    /**
     * @return bool
     */
    public static function canView(): bool
    {
        return true;
    }

}