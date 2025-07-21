<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestPaymentController extends Controller
{
    /**
     * API для получения оплат (для AG-Grid) - без аутентификации
     */
    public function getPayments(Request $request)
    {
        Log::info("TestPaymentController::getPayments: начало обработки запроса", [
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

            $query = Payment::with(['user', 'sellCurrency', 'exchanger']);

            // Применяем фильтры
            if ($statusFilter) {
                $query->where('status', $statusFilter);
            }

            if ($exchangerFilter) {
                $query->where('exchanger', $exchangerFilter);
            }

            $payments = $query->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);

            Log::info("TestPaymentController::getPayments: результат запроса", [
                'total_records' => $payments->total(),
                'current_page' => $payments->currentPage(),
                'per_page' => $payments->perPage(),
                'last_page' => $payments->lastPage(),
                'has_more_pages' => $payments->hasMorePages(),
                'items_count' => count($payments->items())
            ]);

            return response()->json([
                'data' => $payments->items(),
                'total' => $payments->total(),
                'perPage' => $payments->perPage(),
                'currentPage' => $payments->currentPage(),
                'lastPage' => $payments->lastPage(),
                'hasMorePages' => $payments->hasMorePages(),
            ]);
        } catch (\Exception $e) {
            Log::error('TestPaymentController::getPayments error', [
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
