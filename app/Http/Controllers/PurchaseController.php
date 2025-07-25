<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    /**
     * Страница покупки крипты
     */
    public function index()
    {
        return view('pages.purchase');
    }

    /**
     * API для получения покупок крипты (для AG-Grid)
     */
    public function getPurchases(Request $request)
    {
        Log::info("PurchaseController::getPurchases: начало обработки запроса", [
            'page' => $request->get('page', 1),
            'perPage' => $request->get('perPage', 50),
            'statusFilter' => $request->get('statusFilter', ''),
            'exchangerFilter' => $request->get('exchangerFilter', '')
        ]);

        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('perPage', 50);
            $statusFilter = $request->get('statusFilter', '');
            $exchangerFilter = $request->get('exchangerFilter', '');

            $query = Purchase::with(['user', 'sellCurrency', 'buyCurrency']);

            // Применяем фильтры
            if ($statusFilter) {
                $query->where('status', $statusFilter);
            }

            if ($exchangerFilter) {
                $query->where('exchanger', $exchangerFilter);
            }

            $purchases = $query->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);

            Log::info("PurchaseController::getPurchases: результат запроса", [
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
            Log::error('PurchaseController::getPurchases error', [
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
