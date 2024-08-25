<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected $token = '';
    protected $email = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
    }

    #[Test]
    public function an_existing_user_can_reset_his_password(): void
    {
        Notification::fake();
        $data = ['email' => 'example@example.com'];

        $response = $this->postJson("{$this->apiBase}/reset-password", $data);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'OK']);
        $user = User::find(1);
        Notification::assertSentTo([$user], function (ResetPasswordNotification $notification) {
            $url = $notification->url;
            $parts = parse_url($url);
            parse_str($parts['query'], $query);
            $this->token = $query['token'];
            $this->email = $query['email'];
            return str_contains($url, 'http://front.app/reset-password?token=');
        });

        $response = $this->putJson("{$this->apiBase}/reset-password?token={$this->token}", [
            'email' => $this->email,
            'password' => 'newPassword',
            'password_confirmation' => 'newPassword',
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors']);
        $this->assertTrue(Hash::check('newPassword', $user->password));
    }
}
