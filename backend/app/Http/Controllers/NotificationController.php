<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    #[OA\Get(
        path: '/api/notifications',
        tags: ['Notifications'],
        summary: 'Daftar notifikasi (global + milik user)',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List notifikasi'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where(function ($q) use ($request) {
            $q->whereNull('user_id')->orWhere('user_id', $request->user()->id);
        })
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $notifications]);
    }

    #[OA\Patch(
        path: '/api/notifications/{notification}/read',
        tags: ['Notifications'],
        summary: 'Tandai notifikasi sebagai sudah dibaca',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'notification', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Notifikasi ditandai terbaca'),
            new OA\Response(response: 404, description: 'Notifikasi tidak ditemukan'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function markRead(int $notification): JsonResponse
    {
        $notif = Notification::findOrFail($notification);
        $notif->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marked as read']);
    }
}
