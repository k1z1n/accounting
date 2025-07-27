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
            'exchangerFilter' => $request->get('exchangerFilter', ''),
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ]);

        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('perPage', 50);
            $statusFilter = $request->get('statusFilter', '');
            $exchangerFilter = $request->get('exchangerFilter', '');

            $query = Payment::with(['exchanger', 'user', 'sellCurrency']);
                // ->where('user_id', auth()->id()); // Убираем фильтр по пользователю

            // Применяем фильтры (отключены, так как полей status и exchanger нет в миграции)
            // if ($statusFilter) {
            //     $query->where('status', $statusFilter);
            // }

            // if ($exchangerFilter) {
            //     $query->where('exchanger', $exchangerFilter);
            // }

            $payments = $query->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);

            Log::info("PaymentController::getPayments: SQL запрос", [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            Log::info("PaymentController::getPayments: результат запроса", [
                'total_records' => $payments->total(),
                'current_page' => $payments->currentPage(),
                'per_page' => $payments->perPage(),
                'last_page' => $payments->lastPage(),
                'has_more_pages' => $payments->hasMorePages(),
                'items_count' => count($payments->items()),
                'first_item_id' => $payments->items()[0]->id ?? 'none',
                'last_item_id' => $payments->items()[count($payments->items()) - 1]->id ?? 'none',
                'debug_info' => [
                    'current_page_vs_last_page' => $payments->currentPage() . ' vs ' . $payments->lastPage(),
                    'should_have_more' => $payments->currentPage() < $payments->lastPage(),
                    'total_vs_current_items' => $payments->total() . ' vs ' . ($payments->currentPage() * $payments->perPage())
                ]
            ]);

            return response()->json([
                'data' => $payments->items(),
                'total' => $payments->total(),
                'perPage' => $payments->perPage(),
                'currentPage' => $payments->currentPage(),
                'lastPage' => $payments->lastPage(),
                'hasMorePages' => $payments->hasMorePages(),
                'debug' => [
                    'user_id' => auth()->id(),
                    'timestamp' => now()->toISOString(),
                    'items_count' => count($payments->items())
                ]
        ]);
        } catch (\Exception $e) {
            Log::error('PaymentController::getPayments error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'error' => 'Ошибка загрузки данных',
                'data' => [],
                'total' => 0,
                'perPage' => 50,
                'currentPage' => 1,
                'lastPage' => 1,
                'hasMorePages' => false,
                'debug' => [
                    'user_id' => auth()->id(),
                    'timestamp' => now()->toISOString(),
                    'error' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->exchanger_id = $request->input('exchanger_id');
        $payment->user_id = auth()->id(); // Автоматически назначаем текущего пользователя
        $payment->sell_amount = $request->input('sell_amount');
        $payment->sell_currency_id = $request->input('sell_currency_id');
        $payment->comment = $request->input('comment');
        $payment->save();
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        try {
            // Ищем запись с проверкой прав доступа
            $payment = Payment::where('id', $id)
                ->where('user_id', auth()->id()) // Проверяем, что запись принадлежит текущему пользователю
                ->first();

            if (!$payment) {
                return response()->json([
                    'message' => 'Платеж не найден или у вас нет прав для его удаления.'
                ], 404);
            }

            $payment->delete();

            return response()->json([
                'message' => 'Платеж успешно удален.'
            ]);
        } catch (\Exception $e) {
            Log::error('PaymentController::destroy error', [
                'id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Ошибка при удалении платежа: ' . $e->getMessage()
            ], 500);
        }
    }
}
