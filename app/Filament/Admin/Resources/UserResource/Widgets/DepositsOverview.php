<?php

namespace App\Filament\Admin\Resources\UserResource\Widgets;

use App\Models\Deposit;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DepositsOverview extends BaseWidget
{
    public User $record;

    /**
     * @return array|Stat[]
     */
    protected function getStats(): array
    {
        $totalDeposits = Deposit::where('user_id', $this->record->id)->sum('amount');
        $totalDepositsConfirmed = Deposit::where('user_id', $this->record->id)->where('status', 1)->sum('amount');
        $totalDepositsUnConfirmed = Deposit::where('user_id', $this->record->id)->where('status', 0)->sum('amount');

        return [
            Stat::make('Total de Depósitos', $totalDeposits),
            Stat::make('Total de Depósitos Confirmado', $totalDepositsConfirmed),
            Stat::make('Total de Pendente', $totalDepositsUnConfirmed),
        ];
    }
}
