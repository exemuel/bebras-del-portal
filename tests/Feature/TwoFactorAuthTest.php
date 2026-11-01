<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    private function adminWithTwoFactor(): array
    {
        $secret = (new Google2FA())->generateSecretKey();

        $user = User::factory()->create(['password' => 'Secret-pass-123']);
        $user->saveAppAuthenticationSecret($secret);

        return [$user, $secret];
    }

    public function test_user_without_two_factor_logs_in_with_password_only(): void
    {
        $user = User::factory()->create(['password' => 'Secret-pass-123']);

        Livewire::test(Login::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'Secret-pass-123')
            ->call('authenticate')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_with_two_factor_is_challenged_and_logs_in_with_valid_code(): void
    {
        [$user, $secret] = $this->adminWithTwoFactor();

        $component = Livewire::test(Login::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'Secret-pass-123')
            ->call('authenticate');

        $this->assertGuest();

        $component
            ->set('data.multiFactor.app.code', (new Google2FA())->getCurrentOtp($secret))
            ->call('authenticate')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_code_does_not_log_in(): void
    {
        [$user] = $this->adminWithTwoFactor();

        Livewire::test(Login::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'Secret-pass-123')
            ->call('authenticate')
            ->set('data.multiFactor.app.code', '000000')
            ->call('authenticate')
            ->assertHasErrors();

        $this->assertGuest();
    }

    public function test_wrong_password_never_reaches_the_challenge(): void
    {
        [$user] = $this->adminWithTwoFactor();

        Livewire::test(Login::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'not-the-password')
            ->call('authenticate')
            ->assertHasErrors();

        $this->assertGuest();
    }

    public function test_recovery_code_logs_in_once_and_is_then_consumed(): void
    {
        [$user] = $this->adminWithTwoFactor();

        $plainCodes = ['AAAA-1111', 'BBBB-2222'];
        $user->saveAppAuthenticationRecoveryCodes(array_map(fn (string $code) => Hash::make($code), $plainCodes));

        Livewire::test(Login::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'Secret-pass-123')
            ->call('authenticate')
            ->set('data.multiFactor.app.useRecoveryCode', true)
            ->set('data.multiFactor.app.recoveryCode', 'AAAA-1111')
            ->call('authenticate')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
        $this->assertCount(1, $user->fresh()->getAppAuthenticationRecoveryCodes());
    }

    public function test_secret_is_encrypted_at_rest_and_hidden(): void
    {
        [$user, $secret] = $this->adminWithTwoFactor();

        $raw = \DB::table('users')->where('id', $user->id)->value('app_authentication_secret');

        $this->assertNotSame($secret, $raw);
        $this->assertArrayNotHasKey('app_authentication_secret', $user->fresh()->toArray());
        $this->assertTrue(AppAuthentication::make()->isEnabled($user->fresh()));
    }

    public function test_reset_command_disables_two_factor(): void
    {
        [$user] = $this->adminWithTwoFactor();
        $user->saveAppAuthenticationRecoveryCodes([Hash::make('AAAA-1111')]);

        $this->artisan('bebras:2fa-reset', ['email' => $user->email])->assertSuccessful();

        $fresh = $user->fresh();
        $this->assertNull($fresh->getAppAuthenticationSecret());
        $this->assertNull($fresh->getAppAuthenticationRecoveryCodes());

        $this->artisan('bebras:2fa-reset', ['email' => 'nobody@example.com'])->assertFailed();
    }
}
