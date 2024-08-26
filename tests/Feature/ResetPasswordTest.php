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

    protected string $token = '';
    protected string $email = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
    }

    #[Test]
    public function an_existing_user_can_reset_his_password(): void
    {
        $this->sendResetPassword();

        $response = $this->putJson("{$this->apiBase}/reset-password?token={$this->token}", [
            'email' => $this->email,
            'password' => 'newPassword',
            'password_confirmation' => 'newPassword',
        ]);
        $user = User::find(1);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors']);
        $this->assertTrue(Hash::check('newPassword', $user->password));
    }

    public function sendResetPassword(): void
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
    }

    #[Test]
    public function email_must_be_required()
    {
        $data = ['email' => ''];

        $response = $this->postJson("{$this->apiBase}/reset-password", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['email'], 'status', 'message']);
    }

    #[Test]
    public function email_must_be_valid()
    {
        $data = ['email' => 'sdffff'];

        $response = $this->postJson("{$this->apiBase}/reset-password", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['email'], 'status', 'message']);
    }

    #[Test]
    public function email_must_be_existing()
    {
        $data = ['email' => 'not-existing@example.com'];

        $response = $this->postJson("{$this->apiBase}/reset-password", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['email'], 'status', 'message']);
    }

    #[Test]
    public function email_must_be_associated_with_token()
    {
        $this->sendResetPassword();
        $this->email = 'fake@example.com';

        $response = $this->putJson("{$this->apiBase}/reset-password?token={$this->token}", [
            'email' => $this->email,
            'password' => 'newPassword',
            'password_confirmation' => 'newPassword',
        ]);

        $response->assertStatus(500);
        $response->assertJsonStructure(['message', 'data', 'status']);
        $response->assertJsonFragment([
            'message' => 'Invalid email',
        ]);
    }

    #[Test]
    public function password_must_be_required(): void
    {
        $this->sendResetPassword();

        $response = $this->putJson("{$this->apiBase}/reset-password?token={$this->token}", [
            'email' => $this->email,
            'password' => '',
            'password_confirmation' => 'newPassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['password']]);
    }

    #[Test]
    public function password_must_be_confirmed(): void
    {
        $this->sendResetPassword();

        $response = $this->putJson("{$this->apiBase}/reset-password?token={$this->token}", [
            'email' => $this->email,
            'password' => 'newPassword',
            'password_confirmation' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['password']]);
    }

    #[Test]
    public function token_must_be_valid(): void
    {
        $this->sendResetPassword();
        $this->token = 'wrongToken';

        $response = $this->putJson("{$this->apiBase}/reset-password?token={$this->token}", [
            'email' => $this->email,
            'password' => 'newPassword',
            'password_confirmation' => 'newPassword',
        ]);

        $response->assertStatus(500);
        $response->assertJsonStructure(['message', 'data', 'status']);
        $response->assertJsonFragment([
            'message' => 'Invalid Token',
        ]);
    }

}
