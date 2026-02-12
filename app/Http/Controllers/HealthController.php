<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'service' => 'app',
        ], 200);
    }

    public function healthDb(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            DB::select('select 1');

            return response()->json([
                'ok' => true,
                'service' => 'db',
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'service' => 'db',
                'error' => 'db_unreachable',
            ], 500);
        }
    }
}
