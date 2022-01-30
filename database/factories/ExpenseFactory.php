<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Category;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
//    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->paragraph(),
            'amount' => '50',
            'expense_date' => Carbon::now()->subDays(5),
            'account_id' => Account::factory()->create()->id,
            'category_id' => Category::factory()->create()->id
        ];
    }
}
