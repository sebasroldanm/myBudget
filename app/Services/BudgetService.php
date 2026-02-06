<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;

class BudgetService
{
    /**
     * Generate budget items for a given budget based on recurring products.
     * 
     * @param Budget $budget
     * @return int Number of items created
     */
    public function generateBudgetItems(Budget $budget): int
    {
        $products = Product::active()
            ->recurring()
            ->get();

        $count = 0;

        foreach ($products as $product) {
            // 1. Calculate effective date range (Intersection between Budget and Product)
            $startDate = $budget->period_start->max($product->start_date ?? $budget->period_start);
            $endDate = $budget->period_end->min($product->end_date ?? $budget->period_end);

            if ($startDate->gt($endDate)) {
                continue;
            }

            // 2. Generate partial candidate dates based on periodicity
            $dates = $this->calculateOccurrenceDates($product, $startDate, $endDate);

            foreach ($dates as $date) {
                // 3. Validation: Avoid duplicates (same product, same date, same amount) - GLOBAL check
                // "No se puede crear budgetItem identicos (mismo expected_amount y payment_date en otro budget)"
                $exists = BudgetItem::where('product_id', $product->id)
                    ->whereDate('payment_date', $date)
                    ->where('expected_amount', $product->expected_price ?? $product->price)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // 4. Create BudgetItem
                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'product_id' => $product->id,
                    'expected_amount' => $product->expected_price ?? $product->price,
                    'actual_amount' => 0,
                    'payment_date' => $date,
                    'is_paid' => false,
                    'account_id' => $product->default_account_id,
                ]);

                $count++;
            }
        }

        return $count;
    }

    /**
     * Calculate occurrence dates for a product within a specific range.
     * 
     * @param Product $product
     * @param Carbon $start Range start
     * @param Carbon $end Range end
     * @return array<Carbon>
     */
    private function calculateOccurrenceDates(Product $product, Carbon $start, Carbon $end): array
    {
        if (!$product->payment_date) {
            return [];
        }

        $dates = [];
        $periodicity = strtolower($product->periodicity ?? 'monthly'); // Default to monthly if null

        // Anchor date is the product's payment date setting
        // We need to project this anchor date into the future/past to find matches in [start, end]
        // Strategy: Start from product's initial payment date and iterate until we pass $end.
        // Collect those >= $start and <= $end.
        
        // However, if product started way back, iterating from product->payment_date might be inefficient.
        // But for "monthly" or "yearly" it's fine. For "daily", we might want to optimize.
        // Let's assume standard iterating is okay for now, or optimize start point.
        
        $current = $product->payment_date->copy();
        
        // If current is before start, jump closer to start to avoid useless loops
        // This is tricky with variable months. Safer to iterate if gaps aren't huge.
        // Optimization: If daily, just jump to start.
        if ($periodicity === 'daily' && $current->lt($start)) {
             $current = $start->copy();
        }

        // Loop while current date is <= range end
        while ($current->lte($end)) {
            // If current date is inside our effective range, add it
            if ($current->gte($start)) {
                $dates[] = $current->copy();
            }

            // Move to next occurrence
            $current = $this->incrementDate($current, $periodicity);
        }

        return $dates;
    }

    private function incrementDate(Carbon $date, string $periodicity): Carbon
    {
        return match ($periodicity) {
            'daily' => $date->addDay(),
            'weekly' => $date->addWeek(),
            'yearly' => $date->addYear(),
            default => $date->addMonth(), // Default 'monthly'
        };
    }

    public function updateBudgetItem(Transaction $transaction): void
    {
        $budgetItem = BudgetItem::find($transaction->budget_item_id);
            if ($budgetItem) {
                $budgetItem->update([
                    'actual_amount' => $transaction->amount,
                    'pay_date' => Carbon::parse($transaction->transaction_date)->format('Y-m-d'),
                    'is_paid' => true,
                ]);
            }
    }
}
