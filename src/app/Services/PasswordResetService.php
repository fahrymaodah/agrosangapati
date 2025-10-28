<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetService
{
    /**
     * Request a password reset token.
     * Creates a reset token and stores it in password_reset_tokens table.
     *
     * @param string $email
     * @return array
     */
    public function requestPasswordReset(string $email): array
    {
        // Find user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Don't reveal if email exists or not for security
            return [
                'success' => true,
                'message' => 'If the email exists, a password reset link will be sent.',
                'email' => $email
            ];
        }

        // Check if user is active
        if ($user->status !== 'active') {
            return [
                'success' => false,
                'message' => 'Your account is inactive. Please contact administrator.',
                'email' => $email
            ];
        }

        // Generate a secure random token
        $token = Str::random(60);

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

        // Store new token
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now()
        ]);

        // In production, send email here
        // Mail::to($user)->send(new PasswordResetMail($token));

        return [
            'success' => true,
            'message' => 'Password reset link has been sent to your email.',
            'email' => $email,
            'token' => $token, // Remove this in production, only for testing
            'user_id' => $user->id,
            'expires_at' => Carbon::now()->addHour()->toDateTimeString()
        ];
    }

    /**
     * Validate a password reset token.
     *
     * @param string $email
     * @param string $token
     * @return array
     */
    public function validateResetToken(string $email, string $token): array
    {
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            return [
                'valid' => false,
                'message' => 'Invalid or expired reset token.'
            ];
        }

        // Check if token is expired (1 hour validity)
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->addHour()->isPast()) {
            // Delete expired token
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();

            return [
                'valid' => false,
                'message' => 'Reset token has expired. Please request a new one.'
            ];
        }

        // Verify token
        if (!Hash::check($token, $resetRecord->token)) {
            return [
                'valid' => false,
                'message' => 'Invalid reset token.'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Token is valid.',
            'email' => $email
        ];
    }

    /**
     * Reset password using the reset token.
     *
     * @param string $email
     * @param string $token
     * @param string $newPassword
     * @return array
     */
    public function resetPassword(string $email, string $token, string $newPassword): array
    {
        // Validate token first
        $validation = $this->validateResetToken($email, $token);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        // Update password
        $user->password = Hash::make($newPassword);
        $user->save();

        // Delete the used token
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

        // Revoke all existing tokens (force re-login)
        $user->tokens()->delete();

        return [
            'success' => true,
            'message' => 'Password has been reset successfully. Please login with your new password.',
            'user_id' => $user->id,
            'email' => $user->email
        ];
    }

    /**
     * Check if a reset token exists for an email.
     *
     * @param string $email
     * @return array
     */
    public function checkResetTokenExists(string $email): array
    {
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            return [
                'exists' => false,
                'message' => 'No reset token found for this email.'
            ];
        }

        // Check if expired
        $createdAt = Carbon::parse($resetRecord->created_at);
        $expiresAt = $createdAt->copy()->addHour();
        
        if ($expiresAt->isPast()) {
            // Delete expired token
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();

            return [
                'exists' => false,
                'message' => 'Reset token has expired.',
                'expired' => true
            ];
        }

        return [
            'exists' => true,
            'message' => 'Reset token exists and is valid.',
            'created_at' => $resetRecord->created_at,
            'expires_at' => $expiresAt->toDateTimeString(),
            'expires_in_minutes' => Carbon::now()->diffInMinutes($expiresAt)
        ];
    }

    /**
     * Cancel/delete a password reset request.
     *
     * @param string $email
     * @return array
     */
    public function cancelResetRequest(string $email): array
    {
        $deleted = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

        if ($deleted) {
            return [
                'success' => true,
                'message' => 'Password reset request has been cancelled.'
            ];
        }

        return [
            'success' => false,
            'message' => 'No active reset request found.'
        ];
    }

    /**
     * Clean up expired tokens (should be run periodically).
     *
     * @return array
     */
    public function cleanupExpiredTokens(): array
    {
        $expiredTime = Carbon::now()->subHour();
        
        $deleted = DB::table('password_reset_tokens')
            ->where('created_at', '<', $expiredTime)
            ->delete();

        return [
            'success' => true,
            'message' => "Cleaned up {$deleted} expired token(s).",
            'deleted_count' => $deleted
        ];
    }
}
