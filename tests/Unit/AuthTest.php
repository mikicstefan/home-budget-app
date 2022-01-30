<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_register_in_the_app()
    {
        $response = $this->json('POST', route('register'), [
            'name' => 'Stefan',
            'email' => 'stefan@test.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(200)->assertJson(function (AssertableJson $json) {
            $json->has('token')
                ->etc();
        });
    }

    /** @test */
    public function user_can_login_in_the_app()
    {
        $user = User::factory()->create();

        $response = $this->json('POST', route('login'), [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200)->assertJson(function (AssertableJson $json) use ($user) {
            $json->has('token')
                ->etc();
        });
    }

    /** @test */
    public function user_can_not_login_in_the_app()
    {
        $user = User::factory()->create();

        $response = $this->json('POST', route('login'), [
            'email' => $user->email,
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_can_logout_from_app()
    {
        // create token with all permissions for this user
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->json('POST', route('logout'));

        $response->assertStatus(200);
    }
}
