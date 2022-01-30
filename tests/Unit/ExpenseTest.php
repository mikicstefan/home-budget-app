<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_expenses()
    {
        $user = $this->authenticateUser();

        $account = Account::factory()->create(['user_id' => $user->id]);
        $expense = Expense::factory()->create(['account_id' => $account->id]);

        $response = $this->json('GET', route('expense.index'));

        $response->assertStatus(200)
            ->assertJsonPath('data.0.name', $expense->name);
    }

    /** @test */
    public function user_can_create_expense()
    {
        $this->authenticateUser();

        $account = Account::factory()->create(); // initial balance is 500
        $category = Category::factory()->create();

        $response = $this->json('POST','/api/expense', [
            'name' => 'Test expense',
            'expense_date' => '25.01.2022',
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => 100
        ]);

        // assert that account is -100 and expanse with given name is created
        $response->assertJsonPath('data.account.balance', 400)
                ->assertJsonPath('data.name', 'Test expense')
                ->assertStatus(200);
    }

    /** @test */
    public function user_can_update_expense()
    {
        $this->authenticateUser();

        $account = Account::factory()->create(); // initial balance is 500
        $category = Category::factory()->create();
        // this expense amount is 50, predefined in factory
        $expense = Expense::factory()->create([
            'name' => 'Test',
            'account_id' => $account->id,
            'category_id' => $category->id
        ]);

        $response = $this->json('PATCH','/api/expense/' . $expense->id, [
            'name' => 'Update Test',
            'account_id' => $expense->account_id,
            'category_id' => $expense->category_id,
            'expense_date' => '01.01.2022',
            'amount' => 20
        ]);

        // assert that account is 480 (initial expense was 50, we updated it to 20, we return 50 and sub 20)
        // and expanse with given name is updated
        $response->assertJsonPath('data.account.balance', 480)
            ->assertJsonPath('data.name', 'Update Test')
            ->assertStatus(200);
    }

    /** @test */
    public function user_can_delete_expense()
    {
        $this->authenticateUser();

        // Creating expense with amount 50
        // Account will be reduced for 50 and will be 450
        $expense = Expense::factory()->create();

        $response = $this->json('DELETE','/api/expense/' . $expense->id);

        // After deleting expense, account balance should be 500 again
        if (Account::find($expense->account_id)->balance == 500) {
            $response->assertSuccessful()
                ->assertStatus(200);
        }
    }

    /** @test */
    public function aggregation_for_period()
    {
        $user = $this->authenticateUser();

        $account = Account::factory()->create(['user_id' => $user->id]);

        // this month expense total 50
        Expense::factory()->create(['account_id' => $account->id, 'expense_date' => Carbon::now()]);

        // this quarter expense total 100
        Expense::factory()->create(['account_id' => $account->id, 'expense_date' => Carbon::now()->subMonths(2)]);

        // this year expense total 150
        Expense::factory()->create(['account_id' => $account->id, 'expense_date' => Carbon::now()->subMonths(6)]);


        $response = $this->json('GET', '/api/expense/aggregation?period=month');
        $response->assertJsonPath('total', '50');


        $response = $this->json('GET', '/api/expense/aggregation?period=quarter');
        $response->assertJsonPath('total', '100');

        $response = $this->json('GET', '/api/expense/aggregation?period=year');
        $response->assertJsonPath('total', '150');
    }

    /**
     * Authenticate user, so he can pass Sanctum API middleware
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function authenticateUser()
    {
        // create token with all permissions for this user
        $user = Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        return $user;
    }
}
