<?php

namespace App\Filament\Resources\Budgets\Widgets;

use App\Models\Budget;
use App\Traits\NumberFormatterTrait;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class AccountBalanceWidget extends BaseWidget
{
    use NumberFormatterTrait;

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

        $sumPayed = 0;

        foreach ($groupedByAccount as $accountId => $items) {
            $account = $items->first()->account;

            if (!$account) continue;

            $sumForAccount = $items->where('is_paid', false)->sum('expected_amount');
            $sumPayed += $items->where('is_paid', true)->sum('actual_amount');
            $totalBudgeted += $sumForAccount;
            $accountBalance = $account->getBalanceBudgetsExchange($this->record->currency);

            if ($sumForAccount < $accountBalance) {
                $color = 'success';
            } else {
                $color = 'danger';
            }

            $stats[] = Stat::make(
                "Consumo: {$account->name}",
                '$ ' . number_format($sumForAccount, 2, ',', '.')
            )
                ->description("Saldo después del presupuesto: " . $this->formatCurrency($accountBalance, $this->record->currency))
                ->color($color);
        }

        $stats[] = Stat::make('Total Presupuestado', number_format($totalBudgeted, 2))
            ->description('Suma de todos los ítems por cuenta')
            ->color('primary');
        // ->chart([2, 4, 6, 8, 10]);

        $stats[] = Stat::make('Pagos realizados', number_format($sumPayed, 2))
            ->description('Suma de todos los pagos realizados')
            ->color('primary');

        return $stats;
    }
}
