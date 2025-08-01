<?php

namespace App\Console\Commands;

use App\Models\SiteCookie;
use Illuminate\Console\Command;

class ManageSiteCookiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site-cookies:manage {action=list} {--name=} {--url=} {--phpsessid=} {--premium-session-id=} {--wordpress-logged-title=} {--wordpress-logged-value=} {--wordpress-sec-title=} {--wordpress-sec-value=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Управление данными сайтов и их cookies в БД';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'list':
                $this->listSites();
                break;
            case 'show':
                $this->showSite();
                break;
            case 'update':
                $this->updateSite();
                break;
            case 'create':
                $this->createSite();
                break;
            case 'delete':
                $this->deleteSite();
                break;
            default:
                $this->error("Неизвестное действие: {$action}");
                $this->info("Доступные действия: list, show, create, update, delete");
                return 1;
        }

        return 0;
    }

    private function listSites()
    {
        $sites = SiteCookie::all();

        if ($sites->isEmpty()) {
            $this->info('Сайты не найдены');
            return;
        }

        $this->info('Список сайтов:');
        $this->table(
            ['ID', 'Название', 'URL', 'PHPSESSID', 'Premium Session ID'],
            $sites->map(function ($site) {
                return [
                    $site->id,
                    $site->name,
                    $site->url,
                    $site->phpsessid ? '✓' : '✗',
                    $site->premium_session_id ? '✓' : '✗',
                ];
            })
        );
    }

    private function showSite()
    {
        $name = $this->option('name');
        if (!$name) {
            $this->error('Укажите --name для просмотра сайта');
            return;
        }

        $site = SiteCookie::where('name', $name)->first();
        if (!$site) {
            $this->error("Сайт '{$name}' не найден");
            return;
        }

        $this->info("Данные сайта: {$site->name}");
        $this->table(
            ['Поле', 'Значение'],
            [
                ['ID', $site->id],
                ['Название', $site->name],
                ['URL', $site->url],
                ['PHPSESSID', $site->phpsessid ?: 'не задан'],
                ['Premium Session ID', $site->premium_session_id ?: 'не задан'],
                ['WordPress Logged Title', $site->wordpress_logged_title ?: 'не задан'],
                ['WordPress Logged Value', $site->wordpress_logged_value ?: 'не задан'],
                ['WordPress Sec Title', $site->wordpress_sec_title ?: 'не задан'],
                ['WordPress Sec Value', $site->wordpress_sec_value ?: 'не задан'],
                ['Создан', $site->created_at],
                ['Обновлен', $site->updated_at],
            ]
        );

        $this->info('Строка cookies:');
        $this->line($site->getCookiesString());
    }

    private function updateSite()
    {
        $name = $this->option('name');
        if (!$name) {
            $this->error('Укажите --name для обновления сайта');
            return;
        }

        $site = SiteCookie::where('name', $name)->first();
        if (!$site) {
            $this->error("Сайт '{$name}' не найден");
            return;
        }

        $data = [];

        if ($this->option('url')) {
            $data['url'] = $this->option('url');
        }
        if ($this->option('phpsessid')) {
            $data['phpsessid'] = $this->option('phpsessid');
        }
        if ($this->option('premium-session-id')) {
            $data['premium_session_id'] = $this->option('premium-session-id');
        }
        if ($this->option('wordpress-logged-title')) {
            $data['wordpress_logged_title'] = $this->option('wordpress-logged-title');
        }
        if ($this->option('wordpress-logged-value')) {
            $data['wordpress_logged_value'] = $this->option('wordpress-logged-value');
        }
        if ($this->option('wordpress-sec-title')) {
            $data['wordpress_sec_title'] = $this->option('wordpress-sec-title');
        }
        if ($this->option('wordpress-sec-value')) {
            $data['wordpress_sec_value'] = $this->option('wordpress-sec-value');
        }

        if (empty($data)) {
            $this->error('Укажите хотя бы одно поле для обновления');
            return;
        }

        $site->update($data);
        $this->info("Сайт '{$name}' обновлен");
    }

    private function createSite()
    {
        $name = $this->option('name');
        $url = $this->option('url');

        if (!$name || !$url) {
            $this->error('Укажите --name и --url для создания сайта');
            return;
        }

        $existingSite = SiteCookie::where('name', $name)->first();
        if ($existingSite) {
            $this->error("Сайт '{$name}' уже существует");
            return;
        }

        $data = [
            'name' => $name,
            'url' => $url,
        ];

        if ($this->option('phpsessid')) {
            $data['phpsessid'] = $this->option('phpsessid');
        }
        if ($this->option('premium-session-id')) {
            $data['premium_session_id'] = $this->option('premium-session-id');
        }
        if ($this->option('wordpress-logged-title')) {
            $data['wordpress_logged_title'] = $this->option('wordpress-logged-title');
        }
        if ($this->option('wordpress-logged-value')) {
            $data['wordpress_logged_value'] = $this->option('wordpress-logged-value');
        }
        if ($this->option('wordpress-sec-title')) {
            $data['wordpress_sec_title'] = $this->option('wordpress-sec-title');
        }
        if ($this->option('wordpress-sec-value')) {
            $data['wordpress_sec_value'] = $this->option('wordpress-sec-value');
        }

        SiteCookie::create($data);
        $this->info("Сайт '{$name}' создан");
    }

    private function deleteSite()
    {
        $name = $this->option('name');
        if (!$name) {
            $this->error('Укажите --name для удаления сайта');
            return;
        }

        $site = SiteCookie::where('name', $name)->first();
        if (!$site) {
            $this->error("Сайт '{$name}' не найден");
            return;
        }

        if ($this->confirm("Вы уверены, что хотите удалить сайт '{$name}'?")) {
            $site->delete();
            $this->info("Сайт '{$name}' удален");
        }
    }
}
