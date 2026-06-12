<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'StockFlow API',
    description: 'Real-time inventory management API — INaAI Competition 2026'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Server(
    url: 'https://stockflow-production-1e0f.up.railway.app',
    description: 'Production (Railway)'
)]
abstract class Controller
{
    //
}
