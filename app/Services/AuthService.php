<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     * @return array{user: User, token: string}
     */
    public function register(array $attributes, ?string $deviceName = null): array
    {
        return DB::transaction(function () use ($attributes, $deviceName): array {
            $user = User::query()->create($attributes);

            return $this->authenticationData($user, $deviceName);
        });
    }

    /** @return array{user: User, token: string}|null */
    public function login(string $email, string $password, ?string $deviceName = null): ?array
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        if (Hash::needsRehash($user->password)) {
            $user->update(['password' => $password]);
        }

        return $this->authenticationData($user, $deviceName);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /** @return array{user: User, token: string} */
    private function authenticationData(User $user, ?string $deviceName): array
    {
        $tokenName = trim((string) $deviceName);
        $tokenName = $tokenName !== '' ? $tokenName : 'api-client';

        return [
            'user' => $user,
            'token' => 'Bearer '.$user->createToken($tokenName)->plainTextToken,
        ];
    }
}
