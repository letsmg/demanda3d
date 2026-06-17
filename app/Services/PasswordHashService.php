<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class PasswordHashService
{
    /**
     * Hash a password using Argon2id with high security parameters.
     *
     * @param  bool  $stretched  Apply extra iterations for critical operations
     */
    public static function hash(string $password, bool $stretched = false): string
    {
        if ($stretched && config('hashing.password_stretching.enabled')) {
            return Hash::make($password, [
                'memory' => config('hashing.argon2id.memory') * 2,
                'time' => config('hashing.argon2id.time') + 1,
                'threads' => config('hashing.argon2id.threads'),
            ]);
        }

        return Hash::make($password);
    }

    /**
     * Verify a password against a hash.
     */
    public static function verify(string $password, string $hash): bool
    {
        return Hash::check($password, $hash);
    }

    /**
     * Check if a hash needs to be re-hashed with new parameters.
     *
     * Useful for migrating to stronger hash parameters over time.
     */
    public static function needsRehash(string $hash): bool
    {
        return Hash::needsRehash($hash, algorithm: 'argon2id', options: [
            'memory' => config('hashing.argon2id.memory'),
            'time' => config('hashing.argon2id.time'),
            'threads' => config('hashing.argon2id.threads'),
        ]);
    }
}
