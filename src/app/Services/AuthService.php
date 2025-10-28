<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        // Hash password
        $data['password'] = Hash::make($data['password']);
        
        // Set default status
        $data['status'] = $data['status'] ?? 'active';
        
        // Create user
        $user = User::create($data);
        
        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Login user
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        // Find user by email
        $user = User::where('email', $credentials['email'])->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is active
        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive. Please contact administrator.'],
            ]);
        }

        // Revoke old tokens if requested
        if (isset($credentials['revoke_old_tokens']) && $credentials['revoke_old_tokens']) {
            $user->tokens()->delete();
        }

        // Generate token
        $tokenName = 'auth_token_' . now()->timestamp;
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'user' => $user->load('poktan'),
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Logout user (revoke current token)
     *
     * @param User $user
     * @return bool
     */
    public function logout(User $user): bool
    {
        // Revoke current token
        $user->currentAccessToken()->delete();
        
        return true;
    }

    /**
     * Logout from all devices (revoke all tokens)
     *
     * @param User $user
     * @return bool
     */
    public function logoutAll(User $user): bool
    {
        // Revoke all tokens
        $user->tokens()->delete();
        
        return true;
    }

    /**
     * Get authenticated user with relations
     *
     * @param User $user
     * @return User
     */
    public function me(User $user): User
    {
        return $user->load(['poktan']);
    }

    /**
     * Refresh token (revoke old and create new)
     *
     * @param User $user
     * @return array
     */
    public function refreshToken(User $user): array
    {
        // Revoke current token
        $user->currentAccessToken()->delete();
        
        // Generate new token
        $tokenName = 'auth_token_' . now()->timestamp;
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Change password
     *
     * @param User $user
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws ValidationException
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        // Verify current password
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        // Update password
        $user->password = Hash::make($newPassword);
        $user->save();

        // Revoke all tokens (force re-login)
        $user->tokens()->delete();

        return true;
    }
}
