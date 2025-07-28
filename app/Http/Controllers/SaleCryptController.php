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
        $applications = \App\Models\Application::orderBy('id', 'desc')->get();
        $exchangers = \App\Models\Exchanger::orderBy('title')->get();
        $currenciesForEdit = \App\Models\Currency::orderBy('code')->get();
        return view('pages.sale-crypt', compact('applications', 'exchangers', 'currenciesForEdit'));
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

            $query = SaleCrypt::with(['exchanger', 'saleCurrency', 'fixedCurrency', 'application']);

            // Применяем фильтры (отключены, так как полей status и exchanger нет в миграции)
            // if ($statusFilter) {
            //     $query->where('status', $statusFilter);
            // }

            // if ($exchangerFilter) {
            //     $query->where('exchanger', $exchangerFilter);
            // }

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

    /**
     * Создание новой продажи
     */
    public function store(Request $request)
    {
        Log::info("SaleCryptController::store: начало создания продажи", [
            'user_id' => auth()->id(),
            'data' => $request->all()
        ]);

        try {
            $validated = $request->validate([
                'application_id' => 'nullable|exists:applications,id',
                'exchanger_id' => 'required|exists:exchangers,id',
                'sale_amount' => 'required|numeric|min:0',
                'sale_currency_id' => 'required|exists:currencies,id',
                'fixed_amount' => 'required|numeric|min:0',
                'fixed_currency_id' => 'required|exists:currencies,id'
            ]);

            $saleCrypt = SaleCrypt::create([
                'user_id' => auth()->id(),
                'application_id' => $validated['application_id'] ?? null,
                'exchanger_id' => $validated['exchanger_id'],
                'sale_amount' => $validated['sale_amount'],
                'sale_currency_id' => $validated['sale_currency_id'],
                'fixed_amount' => $validated['fixed_amount'],
                'fixed_currency_id' => $validated['fixed_currency_id']
            ]);

            Log::info("SaleCryptController::store: продажа успешно создана", [
                'sale_crypt_id' => $saleCrypt->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Продажа успешно создана',
                'saleCrypt' => $saleCrypt->load(['exchanger', 'saleCurrency', 'fixedCurrency', 'application'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("SaleCryptController::store: ошибка валидации", [
                'errors' => $e->errors(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации данных',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error("SaleCryptController::store: ошибка создания продажи", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка создания продажи'
            ], 500);
        }
    }

    public function destroy($id)
    {
        Log::info("SaleCryptController::destroy: начало обработки запроса", ['id' => $id]);
        $saleCrypt = SaleCrypt::findOrFail($id);
        $saleCrypt->delete();
        Log::info("SaleCryptController::destroy: запись успешно удалена", ['id' => $id]);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        Log::info("SaleCryptController::update: начало обработки запроса", [
            'id' => $id,
            'request_data' => $request->all()
        ]);

        $saleCrypt = SaleCrypt::findOrFail($id);
        $saleCrypt->user_id = auth()->id(); // Автоматически назначаем текущего пользователя
        $saleCrypt->application_id = $request->input('application_id');
        $saleCrypt->exchanger_id = $request->input('exchanger_id');
        $saleCrypt->sale_amount = $request->input('sale_amount');
        $saleCrypt->sale_currency_id = $request->input('sale_currency_id');
        $saleCrypt->fixed_amount = $request->input('fixed_amount');
        $saleCrypt->fixed_currency_id = $request->input('fixed_currency_id');
        $saleCrypt->save();

        Log::info("SaleCryptController::update: запись обновлена", [
            'id' => $id,
            'application_id' => $saleCrypt->application_id,
            'exchanger_id' => $saleCrypt->exchanger_id
        ]);

        // Проверяем, что данные действительно сохранились
        $updatedSaleCrypt = SaleCrypt::with('application')->find($id);
        Log::info("SaleCryptController::update: проверка сохраненных данных", [
            'id' => $updatedSaleCrypt->id,
            'application_id' => $updatedSaleCrypt->application_id,
            'application' => $updatedSaleCrypt->application ? [
                'id' => $updatedSaleCrypt->application->id,
                'app_id' => $updatedSaleCrypt->application->app_id
            ] : null
        ]);

        return response()->json(['success' => true]);
    }
}
