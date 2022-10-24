<?php

namespace Tests\Feature;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PublisherTest extends TestCase
{
    public function loginUser(){
        $user = User::factory()->create();

        $response = $this->post('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        return $response['data']['accessToken'];

    }
   //test that an authenticated user cannot publish messages
    public function test_authenticated_user_cannot_publish_messages()
    {
        $response = $this->post('/api/v1/publisher/create', [
            'slug' => '0710492945571573221023',
            'message' => 'test message',
        ]);


        $response->assertStatus(401);
        $response->assertUnauthorized();
        $this->assertEquals(false, $response['success']);
        $this->assertEquals('Unauthenticated', $response['message']);
    }

    //test that an authenticated user can publish messages
    public function test_authenticated_user_can_publish_messages()
    {
        $topic = Topic::factory()->create();

        $token = $this->loginUser();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)->post('/api/v1/publisher/create', [
            'slug' => $topic->slug,
            'message' => 'test message',
        ]);

        $this->assertAuthenticated();
        $response->assertStatus(200);
        $this->assertEquals(true, $response['success']);
        $this->assertEquals("Message dispatched successfully", $response['message']);
    }


}
