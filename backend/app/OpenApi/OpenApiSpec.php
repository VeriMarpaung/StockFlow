<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'StockFlow API',
    version: '1.0.0',
    description: 'Inventory management API with concurrent safety. Optimistic locking on stock-out, Redis queue for async jobs, Redis cache for read-heavy endpoints.'
)]
#[OA\Server(url: 'http://localhost:8000', description: 'Local Development')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'token',
    description: 'Bearer token dari POST /api/auth/login'
)]
class OpenApiSpec
{
}
