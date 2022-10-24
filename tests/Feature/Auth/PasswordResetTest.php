<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\PasswordResetMail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_can_be_requested()
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('api/v1/auth/forgot-password', ['email' => $user->email]);


        //Notification::assertSentTo($user, PasswordResetMail::class);
        $this->assertEquals(true, $response['success']);
        $response->assertStatus(201);
    }


}
