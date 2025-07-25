<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    /**
     * Страница переводов
     */
    public function index()
    {
        return view('pages.transfer');
    }

    /**
     * API для получения переводов (для AG-Grid)
     */
    public function getTransfers(Request $request)
    {
        Log::info("TransferController::getTransfers: начало обработки запроса", [
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

            $query = Transfer::with(['amountCurrency']);

            // Применяем фильтры (отключены, так как полей status и exchanger нет в миграции)
            // if ($statusFilter) {
            //     $query->where('status', $statusFilter);
            // }

            // if ($exchangerFilter) {
            //     $query->where('exchanger', $exchangerFilter);
            // }

            $transfers = $query->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);

            Log::info("TransferController::getTransfers: результат запроса", [
                'total_records' => $transfers->total(),
                'current_page' => $transfers->currentPage(),
                'per_page' => $transfers->perPage(),
                'last_page' => $transfers->lastPage(),
                'has_more_pages' => $transfers->hasMorePages(),
                'items_count' => count($transfers->items())
            ]);

            return response()->json([
                'data' => $transfers->items(),
                'total' => $transfers->total(),
                'perPage' => $transfers->perPage(),
                'currentPage' => $transfers->currentPage(),
                'lastPage' => $transfers->lastPage(),
                'hasMorePages' => $transfers->hasMorePages(),
            ]);
        } catch (\Exception $e) {
            Log::error('TransferController::getTransfers error', [
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
