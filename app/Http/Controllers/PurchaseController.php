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
        $applications = \App\Models\Application::orderBy('id', 'desc')->get();
        $exchangers = \App\Models\Exchanger::orderBy('title')->get();
        $currenciesForEdit = \App\Models\Currency::orderBy('code')->get();
        return view('pages.purchase', compact('applications', 'exchangers', 'currenciesForEdit'));
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

            $query = Purchase::with(['exchanger', 'saleCurrency', 'receivedCurrency', 'application']);

            // Применяем фильтры (отключены, так как полей status и exchanger нет в миграции)
            // if ($statusFilter) {
            //     $query->where('status', $statusFilter);
            // }

            // if ($exchangerFilter) {
            //     $query->where('exchanger', $exchangerFilter);
            // }

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

            // Логируем первую запись для отладки
            if (count($purchases->items()) > 0) {
                $firstItem = $purchases->items()[0];
                Log::info("PurchaseController::getPurchases: первая запись", [
                    'id' => $firstItem->id,
                    'application_id' => $firstItem->application_id,
                    'application' => $firstItem->application ? [
                        'id' => $firstItem->application->id,
                        'app_id' => $firstItem->application->app_id
                    ] : null
                ]);
            }

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

    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);
        $purchase->delete();
        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        Log::info("PurchaseController::update: начало обработки запроса", [
            'id' => $id,
            'request_data' => $request->all()
        ]);

        $purchase = Purchase::findOrFail($id);
        $purchase->user_id = auth()->id(); // Автоматически назначаем текущего пользователя
        $purchase->application_id = $request->input('application_id');
        $purchase->exchanger_id = $request->input('exchanger_id');
        $purchase->sale_amount = $request->input('sale_amount');
        $purchase->sale_currency_id = $request->input('sale_currency_id');
        $purchase->received_amount = $request->input('received_amount');
        $purchase->received_currency_id = $request->input('received_currency_id');
        $purchase->save();

        Log::info("PurchaseController::update: запись обновлена", [
            'id' => $id,
            'application_id' => $purchase->application_id,
            'exchanger_id' => $purchase->exchanger_id
        ]);

        // Проверяем, что данные действительно сохранились
        $updatedPurchase = Purchase::with('application')->find($id);
        Log::info("PurchaseController::update: проверка сохраненных данных", [
            'id' => $updatedPurchase->id,
            'application_id' => $updatedPurchase->application_id,
            'application' => $updatedPurchase->application ? [
                'id' => $updatedPurchase->application->id,
                'app_id' => $updatedPurchase->application->app_id
            ] : null
        ]);

        return response()->json(['success' => true]);
    }
}
