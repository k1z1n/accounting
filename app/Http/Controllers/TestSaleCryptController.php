<?php

namespace App\Http\Controllers;

use App\Models\SaleCrypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestSaleCryptController extends Controller
{
    /**
     * API для получения продаж крипты без аутентификации (для тестирования)
     */
    public function getSaleCrypts(Request $request)
    {
        Log::info("TestSaleCryptController::getSaleCrypts: начало обработки запроса", [
            'page' => $request->get('page', 1),
            'perPage' => $request->get('perPage', 50)
        ]);

        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('perPage', 50);

            $query = SaleCrypt::with(['saleCurrency', 'fixedCurrency', 'exchanger']);

            $saleCrypts = $query->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);

            Log::info("TestSaleCryptController::getSaleCrypts: результат запроса", [
                'total_records' => $saleCrypts->total(),
                'current_page' => $saleCrypts->currentPage(),
                'per_page' => $saleCrypts->perPage(),
                'last_page' => $saleCrypts->lastPage(),
                'has_more_pages' => $saleCrypts->hasMorePages(),
                'items_count' => count($saleCrypts->items())
            ]);

            return response()->json([
                'data' => $saleCrypts->items(),
                'total' => $saleCrypts->total(),
                'perPage' => $saleCrypts->perPage(),
                'currentPage' => $saleCrypts->currentPage(),
                'lastPage' => $saleCrypts->lastPage(),
                'hasMorePages' => $saleCrypts->hasMorePages(),
            ]);
        } catch (\Exception $e) {
            Log::error('TestSaleCryptController::getSaleCrypts error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Ошибка загрузки данных',
                'data' => [],
                'total' => 0,
                'perPage' => 50,
                'currentPage' => 1,
                'lastPage' => 1,
                'hasMorePages' => false,
            ], 500);
        }
    }
}
