<?php

namespace Tests\Feature\Auth;

use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\VerificationCodeNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified_with_valid_code(): void
    {
        $user = User::factory()->unverified()->create();
        $code = EmailVerificationCode::generateFor($user);

        Event::fake();

        $response = $this->actingAs($user)->post('/verify-email/code', [
            'code' => $code->code,
        ]);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_code(): void
    {
        $user = User::factory()->unverified()->create();
        EmailVerificationCode::generateFor($user);

        $response = $this->actingAs($user)->post('/verify-email/code', [
            'code' => '000000',
        ]);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $response->assertSessionHasErrors('code');
    }

    public function test_email_is_not_verified_with_expired_code(): void
    {
        $user = User::factory()->unverified()->create();
        $code = EmailVerificationCode::generateFor($user);

        // Manually expire the code
        $code->update(['expires_at' => now()->subMinutes(1)]);

        $response = $this->actingAs($user)->post('/verify-email/code', [
            'code' => $code->code,
        ]);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $response->assertSessionHasErrors('code');
    }

    public function test_verification_code_can_be_resent(): void
    {
        $user = User::factory()->unverified()->create();

        Notification::fake();

        $response = $this->actingAs($user)->post('/email/verification-notification');

        Notification::assertSentTo($user, VerificationCodeNotification::class);
        $response->assertSessionHas('status', 'verification-code-sent');
    }

    public function test_verified_user_is_redirected_from_verification_screen(): void
    {
        $user = User::factory()->create(); // verified by default

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
