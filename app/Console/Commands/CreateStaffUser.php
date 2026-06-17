<?php

namespace App\Console\Commands;

use App\Enums\UserAccessLevel;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateStaffUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-staff {name} {email} {--password=}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Create a new staff user with Argon2id hashing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
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
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'access_level' => UserAccessLevel::STAFF,
            'email_verified_at' => now(),
        ]);

        $this->info("Staff user {$email} created successfully");

        return 0;
    }
}
