<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Страница оплат
     */
    public function index()
    {
        return view('pages.payments');
    }

    /**
     * API для получения оплат (для AG-Grid)
     */
    public function getPayments(Request $request)
    {
        Log::info("PaymentController::getPayments: начало обработки запроса", [
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

            $query = Payment::with(['user', 'sellCurrency']);

            // Применяем фильтры (отключены, так как полей status и exchanger нет в миграции)
            // if ($statusFilter) {
            //     $query->where('status', $statusFilter);
            // }

            // if ($exchangerFilter) {
            //     $query->where('exchanger', $exchangerFilter);
            // }

            $payments = $query->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);

            Log::info("PaymentController::getPayments: результат запроса", [
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
            Log::error('PaymentController::getPayments error', [
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
