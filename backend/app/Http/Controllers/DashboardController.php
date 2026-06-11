<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Notification;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    #[OA\Get(
        path: '/api/dashboard/summary',
        tags: ['Dashboard'],
        summary: 'Ringkasan inventori (cached Redis 60 detik)',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Dashboard summary',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'total_products', type: 'integer'),
                    new OA\Property(property: 'low_stock_count', type: 'integer'),
                    new OA\Property(property: 'total_categories', type: 'integer'),
                    new OA\Property(property: 'transactions_today', type: 'integer'),
                    new OA\Property(property: 'unread_notifications', type: 'integer'),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function summary(): JsonResponse
    {
        $data = Cache::remember('dashboard:summary', 60, function () {
            return [
                'total_products'       => Product::count(),
                'low_stock_count'      => Product::lowStock()->count(),
                'total_categories'     => Category::count(),
                'transactions_today'   => StockTransaction::whereDate('created_at', today())->count(),
                'unread_notifications' => Notification::whereNull('read_at')->count(),
            ];
        });

        return response()->json($data);
    }
}
