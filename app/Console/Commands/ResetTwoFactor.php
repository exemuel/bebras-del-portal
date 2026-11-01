<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetTwoFactor extends Command
{
    protected $signature = 'bebras:2fa-reset {email : Email of the admin who lost their authenticator}';

    protected $description = 'Disable two-factor authentication for an admin (clears the authenticator secret and recovery codes)';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user found with that email.');

            return self::FAILURE;
        }

        $user->forceFill([
            'app_authentication_secret' => null,
            'app_authentication_recovery_codes' => null,
        ])->save();

        $this->info("Two-factor authentication disabled for {$user->email}. They can enroll again from the Profile page.");

        return self::SUCCESS;
    }
}
