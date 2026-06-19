<?php

namespace App\Console\Commands;

use App\Enums\UserAccessLevel;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin {first_name} {last_name} {email} {--display_name=} {--password=}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Create a new admin user with Argon2id hashing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $firstName = $this->argument('first_name');
        $lastName = $this->argument('last_name');
        $email = $this->argument('email');
        $displayName = $this->option('display_name');
        $password = $this->option('password');

        if (! $password) {
            $password = Str::random(16);
            $this->info("Generated password: {$password}");
        }

        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists");

            return 1;
        }

        User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'display_name' => $displayName,
            'email' => $email,
            'password' => Hash::make($password),
            'access_level' => UserAccessLevel::ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->info("Admin user {$email} created successfully");

        return 0;
    }
}