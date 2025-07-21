<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestPurchaseController extends Controller
{
    /**
     * API для получения покупок без аутентификации (для тестирования)
     */
    public function getPurchases(Request $request)
    {
        Log::info("TestPurchaseController::getPurchases: начало обработки запроса", [
            'page' => $request->get('page', 1),
            'perPage' => $request->get('perPage', 50)
        ]);

        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('perPage', 50);

            $query = Purchase::with(['saleCurrency', 'receivedCurrency', 'exchanger']);

            $purchases = $query->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);

            Log::info("TestPurchaseController::getPurchases: результат запроса", [
                'total_records' => $purchases->total(),
                'current_page' => $purchases->currentPage(),
                'per_page' => $purchases->perPage(),
                'last_page' => $purchases->lastPage(),
                'has_more_pages' => $purchases->hasMorePages(),
                'items_count' => count($purchases->items())
            ]);

            return response()->json([
                'data' => $purchases->items(),
                'total' => $purchases->total(),
                'perPage' => $purchases->perPage(),
                'currentPage' => $purchases->currentPage(),
                'lastPage' => $purchases->lastPage(),
                'hasMorePages' => $purchases->hasMorePages(),
            ]);
        } catch (\Exception $e) {
            Log::error('TestPurchaseController::getPurchases error', [
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
