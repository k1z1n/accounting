<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteCookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SiteCookiesController extends Controller
{
    /**
     * Показать список всех обменников
     */
    public function index()
    {
        $siteCookies = SiteCookie::orderBy('name')->get();
        return view('admin.site-cookies.index', compact('siteCookies'));
    }

    /**
     * Показать форму создания нового обменника
     */
    public function create()
    {
        return view('admin.site-cookies.create');
    }

    /**
     * Сохранить новый обменник
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:site_cookies,name',
            'url' => 'required|url|max:500',
            'phpsessid' => 'nullable|string|max:255',
            'premium_session_id' => 'nullable|string|max:500',
            'wordpress_logged_title' => 'nullable|string|max:255',
            'wordpress_logged_value' => 'nullable|string',
            'wordpress_sec_title' => 'nullable|string|max:255',
            'wordpress_sec_value' => 'nullable|string',
        ]);

        try {
            SiteCookie::create($request->all());

            Log::info('SiteCookie created', ['name' => $request->name]);

            return redirect()->route('admin.site-cookies.index')
                ->with('success', 'Обменник успешно создан');
        } catch (\Exception $e) {
            Log::error('SiteCookie creation failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->withInput()
                ->with('error', 'Ошибка создания обменника: ' . $e->getMessage());
        }
    }

    /**
     * Показать форму редактирования обменника
     */
    public function edit(SiteCookie $siteCookie)
    {
        return view('admin.site-cookies.edit', compact('siteCookie'));
    }

    /**
     * Обновить данные обменника
     */
    public function update(Request $request, SiteCookie $siteCookie)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:site_cookies,name,' . $siteCookie->id,
            'url' => 'required|url|max:500',
            'phpsessid' => 'nullable|string|max:255',
            'premium_session_id' => 'nullable|string|max:500',
            'wordpress_logged_title' => 'nullable|string|max:255',
            'wordpress_logged_value' => 'nullable|string',
            'wordpress_sec_title' => 'nullable|string|max:255',
            'wordpress_sec_value' => 'nullable|string',
        ]);

        try {
            $siteCookie->update($request->all());

            Log::info('SiteCookie updated', [
                'id' => $siteCookie->id,
                'name' => $request->name
            ]);

            return redirect()->route('admin.site-cookies.index')
                ->with('success', 'Обменник успешно обновлен');
        } catch (\Exception $e) {
            Log::error('SiteCookie update failed', [
                'id' => $siteCookie->id,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->withInput()
                ->with('error', 'Ошибка обновления обменника: ' . $e->getMessage());
        }
    }

    /**
     * Удалить обменник
     */
    public function destroy(SiteCookie $siteCookie)
    {
        try {
            $name = $siteCookie->name;
            $siteCookie->delete();

            Log::info('SiteCookie deleted', ['name' => $name]);

            return redirect()->route('admin.site-cookies.index')
                ->with('success', 'Обменник успешно удален');
        } catch (\Exception $e) {
            Log::error('SiteCookie deletion failed', [
                'id' => $siteCookie->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Ошибка удаления обменника: ' . $e->getMessage());
        }
    }

    /**
     * Показать детали обменника
     */
    public function show(SiteCookie $siteCookie)
    {
        return view('admin.site-cookies.show', compact('siteCookie'));
    }

    /**
     * API для получения данных обменника
     */
    public function apiShow(SiteCookie $siteCookie)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $siteCookie->id,
                'name' => $siteCookie->name,
                'url' => $siteCookie->url,
                'phpsessid' => $siteCookie->phpsessid,
                'premium_session_id' => $siteCookie->premium_session_id,
                'wordpress_logged_title' => $siteCookie->wordpress_logged_title,
                'wordpress_logged_value' => $siteCookie->wordpress_logged_value,
                'wordpress_sec_title' => $siteCookie->wordpress_sec_title,
                'wordpress_sec_value' => $siteCookie->wordpress_sec_value,
                'cookies_string' => $siteCookie->getCookiesString(),
                'created_at' => $siteCookie->created_at,
                'updated_at' => $siteCookie->updated_at,
            ]
        ]);
    }

    /**
     * Тестирование подключения к обменнику
     */
    public function testConnection(Request $request, SiteCookie $siteCookie)
    {
        try {
            // Если переданы данные формы, используем их для тестирования
            if ($request->has('url')) {
                $testUrl = $request->get('url');
                $testCookies = [];

                if ($request->get('phpsessid')) {
                    $testCookies[] = "PHPSESSID=" . $request->get('phpsessid');
                }
                if ($request->get('premium_session_id')) {
                    $testCookies[] = "premium_session_id=" . $request->get('premium_session_id');
                }
                if ($request->get('wordpress_logged_title') && $request->get('wordpress_logged_value')) {
                    $testCookies[] = $request->get('wordpress_logged_title') . "=" . $request->get('wordpress_logged_value');
                }
                if ($request->get('wordpress_sec_title') && $request->get('wordpress_sec_value')) {
                    $testCookies[] = $request->get('wordpress_sec_title') . "=" . $request->get('wordpress_sec_value');
                }

                $cookieString = implode('; ', $testCookies);
            } else {
                // Используем сохраненные данные
                $testUrl = $siteCookie->url;
                $cookieString = $siteCookie->getCookiesString();
            }

            // Сначала пробуем основную страницу с коротким timeout
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Cookie' => $cookieString,
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])->timeout(8)->get($testUrl);

                $body = $response->body();
                $usedRootUrl = false;

            } catch (\Exception $e) {
                // Если основная страница медленная, пробуем корневую
                $rootUrl = str_replace('/wp-admin/admin.php?page=pn_bids', '', $testUrl);
                $rootUrl = rtrim($rootUrl, '/');

                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Cookie' => $cookieString,
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])->timeout(5)->get($rootUrl);

                $body = $response->body();
                $usedRootUrl = true;
            }

            // Более строгая проверка авторизации
            $isLoginPage = stripos($body, 'wp-login') !== false ||
                          stripos($body, 'wp-admin') === false ||
                          stripos($body, 'wp-admin-bar') === false;

            $hasApplications = stripos($body, 'one_bids_wrap') !== false;
            $hasDashboard = stripos($body, 'wp-admin') !== false;
            $hasLogout = stripos($body, 'wp-logout') !== false;
            $hasUserMenu = stripos($body, 'wp-admin-bar') !== false;

            // Проверяем ошибки авторизации
            $hasAuthError = stripos($body, 'неверный пароль') !== false ||
                           stripos($body, 'invalid password') !== false ||
                           stripos($body, 'access denied') !== false ||
                           stripos($body, 'доступ запрещен') !== false ||
                           stripos($body, 'войдите в систему') !== false ||
                           stripos($body, 'please log in') !== false;

            // Определяем статус авторизации
            $isAuthorized = $response->successful() &&
                           !$isLoginPage &&
                           !$hasAuthError &&
                           ($hasDashboard || $hasUserMenu);

            $result = [
                'success' => $response->successful(),
                'status' => $response->status(),
                'content_length' => strlen($body),
                'is_login_page' => $isLoginPage,
                'has_applications' => $hasApplications,
                'has_dashboard' => $hasDashboard,
                'has_logout' => $hasLogout,
                'has_user_menu' => $hasUserMenu,
                'has_auth_error' => $hasAuthError,
                'is_authorized' => $isAuthorized,
                'cookie_string_length' => strlen($cookieString),
                'used_root_url' => $usedRootUrl,
            ];

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
