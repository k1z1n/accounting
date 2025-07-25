<?php

namespace App\Http\Controllers;

use App\Models\SaleCrypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SaleCryptController extends Controller
{
    /**
     * Страница продажи крипты
     */
    public function index()
    {
        return view('pages.sale-crypt');
    }

    /**
     * API для получения продаж крипты (для AG-Grid)
     */
    public function getSaleCrypts(Request $request)
    {
        Log::info("SaleCryptController::getSaleCrypts: начало обработки запроса", [
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

            $query = SaleCrypt::with(['user', 'sellCurrency', 'buyCurrency']);

            // Применяем фильтры
            if ($statusFilter) {
                $query->where('status', $statusFilter);
            }

            if ($exchangerFilter) {
                $query->where('exchanger', $exchangerFilter);
            }

            $saleCrypts = $query->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);

            Log::info("SaleCryptController::getSaleCrypts: результат запроса", [
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
            Log::error('SaleCryptController::getSaleCrypts error', [
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
