<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    protected PasswordResetService $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * Request a password reset token.
     * 
     * @OA\Post(
     *     path="/api/password/forgot",
     *     summary="Request password reset",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reset link sent (or email not found - same response for security)"
     *     )
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $result = $this->passwordResetService->requestPasswordReset(
            $request->input('email')
        );

        // Always return 200 to prevent email enumeration
        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'email' => $result['email'],
                // In development, include token. Remove in production!
                'token' => $result['token'] ?? null,
                'expires_at' => $result['expires_at'] ?? null,
            ]
        ], 200);
    }

    /**
     * Validate a password reset token.
     * 
     * @OA\Post(
     *     path="/api/password/validate-token",
     *     summary="Validate reset token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "token"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Token is valid"),
     *     @OA\Response(response=400, description="Token is invalid or expired")
     * )
     */
    public function validateToken(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $result = $this->passwordResetService->validateResetToken(
            $request->input('email'),
            $request->input('token')
        );

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'email' => $result['email']
            ]
        ], 200);
    }

    /**
     * Reset password using token.
     * 
     * @OA\Post(
     *     path="/api/password/reset",
     *     summary="Reset password",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "token", "password", "password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password reset successfully"),
     *     @OA\Response(response=400, description="Invalid token or validation failed")
     * )
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $result = $this->passwordResetService->resetPassword(
            $request->input('email'),
            $request->input('token'),
            $request->input('password')
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'email' => $result['email']
            ]
        ], 200);
    }

    /**
     * Check if a reset token exists for an email.
     * Useful for UI to show "you already have a pending reset request".
     * 
     * @OA\Get(
     *     path="/api/password/check-token/{email}",
     *     summary="Check if reset token exists",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="email")
     *     ),
     *     @OA\Response(response=200, description="Token status")
     * )
     */
    public function checkToken(string $email): JsonResponse
    {
        $result = $this->passwordResetService->checkResetTokenExists($email);

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200);
    }

    /**
     * Cancel a password reset request.
     * Allows user to cancel if they didn't request it.
     * 
     * @OA\Delete(
     *     path="/api/password/cancel",
     *     summary="Cancel password reset request",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Reset request cancelled")
     * )
     */
    public function cancelResetRequest(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $result = $this->passwordResetService->cancelResetRequest(
            $request->input('email')
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message']
        ], 200);
    }

    /**
     * Cleanup expired tokens (admin/cron endpoint).
     * Should be called periodically by a scheduled task.
     * 
     * @OA\Post(
     *     path="/api/password/cleanup-expired",
     *     summary="Cleanup expired reset tokens",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Cleanup completed")
     * )
     */
    public function cleanupExpired(): JsonResponse
    {
        $result = $this->passwordResetService->cleanupExpiredTokens();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'deleted_count' => $result['deleted_count']
            ]
        ], 200);
    }
}
