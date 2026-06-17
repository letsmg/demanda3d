<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default hash driver that will be used to hash
    | application passwords. By default, the "bcrypt" algorithm is used; however,
    | you remain free to modify this option if you wish to use another driver.
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => 'argon2id',

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    |
    | This option allows you to control the rounds that will be used when
    | hashing passwords with the Bcrypt algorithm. By default we will
    | increase this on a yearly basis to tackle plain text forces better.
    |
    */

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the amount of time it takes to hash
    | the given password. You may change these options to your own desired
    | values, but these aren't recommended by many security experts out of
    | the box.
    |
    | For production environments with high security requirements, use:
    | time_cost: 4 (minimum iterations)
    | memory_cost: 65536 (64MB)
    | threads: 4
    |
    */

    'argon' => [
        'memory' => env('ARGON_MEMORY', 65536), // 64MB - Default recommended for production
        'threads' => env('ARGON_THREADS', 4),   // Number of parallel threads
        'time' => env('ARGON_TIME', 4),         // Minimum time cost
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon2id Options
    |--------------------------------------------------------------------------
    |
    | Argon2id is the hybrid mode that provides both time and memory hardness.
    | This is the recommended default as it offers the best balance of security.
    |
    | For maximum security against various attack vectors:
    | memory_cost: 65536 (64MB)
    | time_cost: 4 (minimum iterations, can be increased to 5)
    | parallelism: 4 (number of threads)
    |
    | Increasing time_cost to 5+ for critical operations (registration, password change)
    |
    */

    'argon2id' => [
        'memory' => env('ARGON2ID_MEMORY', 65536),  // 64MB for high security
        'time' => env('ARGON2ID_TIME', 4),          // Minimum time cost (adjust to 5 for extra security)
        'threads' => env('ARGON2ID_THREADS', 4),    // Parallel threads
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Stretching
    |--------------------------------------------------------------------------
    |
    | Force password hashing to use additional iterations beyond the minimum.
    | Useful for high-security operations like account registration.
    |
    */

    'password_stretching' => [
        'enabled' => env('PASSWORD_STRETCHING', false),
        'iterations' => 5,
    ],
];
