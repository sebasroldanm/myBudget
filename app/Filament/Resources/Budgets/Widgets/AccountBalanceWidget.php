<?php

namespace App\Filament\Resources\Budgets\Widgets;

use App\Models\Budget;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class AccountBalanceWidget extends BaseWidget
{
    public ?Model $record = null;

    protected ?string $pollingInterval = '5s';

    protected function getStats(): array
    {
        if (! $this->record || ! ($this->record instanceof Budget)) {
            return [];
        }

        // Fetch accounts associated with budget items
        // We want unique accounts that are used in this budget's items
        $budgetItems = $this->record->budgetItems()
            ->with('account')
            ->get();

        $groupedByAccount = $budgetItems->groupBy('account_id');

        $stats = [];
        $totalBudgeted = 0;

        foreach ($groupedByAccount as $accountId => $items) {
            // Obtenemos el modelo de la cuenta (del primer ítem del grupo)
            $account = $items->first()->account;

            if (!$account) continue;

            // Sumamos el monto de los ítems para esta cuenta específica
            $sumForAccount = $items->sum('expected_amount'); // Cambia a expected_amount si lo prefieres
            $totalBudgeted += $sumForAccount;

            if ($sumForAccount < $account->balance) {
                $color = 'success';
            } else {
                $color = 'danger';
            }

            $stats[] = Stat::make(
                "Consumo: {$account->name}",
                number_format($sumForAccount, 2)
            )
                ->description("Saldo actual cuenta: {$account->formatted_balance}")
                ->color($color);
        }

        // Card del Total
        // Assuming all accounts are in the same currency or normalized. 
        // For simplicity we sum up directly, but ideally we should handle currencies.
        $stats[] = Stat::make('Total Presupuestado', number_format($totalBudgeted, 2))
            ->description('Suma de todos los ítems por cuenta')
            ->color('primary');
            // ->chart([2, 4, 6, 8, 10]);

        return $stats;
    }
}
