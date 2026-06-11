<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/auth/login',
        tags: ['Auth'],
        summary: 'Login dan dapatkan Bearer token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@stockflow.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login berhasil',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'token', type: 'string'),
                    new OA\Property(property: 'user', type: 'object', properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'email', type: 'string'),
                        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'staff']),
                    ]),
                ])
            ),
            new OA\Response(response: 422, description: 'Kredensial salah'),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User $user */
        $user  = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    #[OA\Post(
        path: '/api/auth/logout',
        tags: ['Auth'],
        summary: 'Logout dan revoke token',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Logout berhasil',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Logged out'),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    #[OA\Get(
        path: '/api/auth/me',
        tags: ['Auth'],
        summary: 'Profil user yang sedang login',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Data user',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'role', type: 'string', enum: ['admin', 'staff']),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
