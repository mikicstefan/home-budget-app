<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function add_amount_to_the_balance()
    {
        // create token with all permissions for this user
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $account = Account::factory()->create(); // balance 500

        $response = $this->json('PATCH', "/api/account/$account->id/add-balance", [
            'amount' => '20'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.balance', 520);

    }
}
