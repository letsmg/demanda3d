<?php

// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Services;

use App\Models\User;

class UserService
{
    /**
     * Create a user with parity fields (hash + encrypted) for sensitive data.
     */
    public function create(array $data): User
    {
        $data = $this->applyParityFields($data);

        // Remove plain text fields (email kept for Fortify auth)
        unset($data['first_name'], $data['last_name']);

        return User::create($data);
    }

    /**
     * Update a user with parity fields for sensitive data.
     */
    public function update(User $user, array $data): User
    {
        $data = $this->applyParityFields($data);

        // Remove plain text fields (email kept for Fortify auth)
        unset($data['first_name'], $data['last_name']);

        $user->update($data);

        return $user;
    }

    /**
     * Apply LGPD parity structure: build *_hash and *_encrypted for sensitive fields.
     */
    private function applyParityFields(array $data): array
    {
        // First name parity
        if (isset($data['first_name'])) {
            $data = EncryptionService::buildEncryptedFields($data, 'first_name');
        }

        // Last name parity
        if (isset($data['last_name'])) {
            $data = EncryptionService::buildEncryptedFields($data, 'last_name');
        }

        return $data;
    }
}
