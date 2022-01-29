<?php

namespace App\Observers;

use App\Models\Expense;
use Illuminate\Support\Facades\Log;

class ExpenseObserver
{
    /**
     * Handle the Expense "created" event.
     *
     * @param  \App\Models\Expense  $expense
     * @return void
     */
    public function created(Expense $expense)
    {
        $account = $expense->account;

        $account->balance -= $expense->amount;
        $account->save();
    }

    /**
     * Handle the Expense "updated" event.
     *
     * @param  \App\Models\Expense  $expense
     * @return void
     */
    public function updated(Expense $expense)
    {
        if ($expense->isDirty('amount')) {
            $account = $expense->account;

            $account->balance += $expense->getOriginal('amount') - $expense->amount;
            $account->save();
        }
    }

    /**
     * Handle the Expense "deleted" event.
     *
     * @param  \App\Models\Expense  $expense
     * @return void
     */
    public function deleted(Expense $expense)
    {
        $account = $expense->account;

        $account->balance += $expense->amount;
        $account->save();
    }
}
