<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteCookie extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'phpsessid',
        'premium_session_id',
        'wordpress_logged_title',
        'wordpress_logged_value',
        'wordpress_sec_title',
        'wordpress_sec_value',
    ];

    /**
     * Получить полную строку cookies для использования в запросах
     */
    public function getCookiesString(): string
    {
        $cookies = [];

        if ($this->phpsessid) {
            $cookies[] = "PHPSESSID={$this->phpsessid}";
        }

        if ($this->premium_session_id) {
            $cookies[] = "premium_session_id={$this->premium_session_id}";
        }

        if ($this->wordpress_logged_title && $this->wordpress_logged_value) {
            $cookies[] = "{$this->wordpress_logged_title}={$this->wordpress_logged_value}";
        }

        if ($this->wordpress_sec_title && $this->wordpress_sec_value) {
            $cookies[] = "{$this->wordpress_sec_title}={$this->wordpress_sec_value}";
        }

        return implode('; ', $cookies);
    }

    /**
     * Получить массив cookies для использования в HTTP клиенте
     */
    public function getCookiesArray(): array
    {
        $cookies = [];

        if ($this->phpsessid) {
            $cookies['PHPSESSID'] = $this->phpsessid;
        }

        if ($this->premium_session_id) {
            $cookies['premium_session_id'] = $this->premium_session_id;
        }

        if ($this->wordpress_logged_title && $this->wordpress_logged_value) {
            $cookies[$this->wordpress_logged_title] = $this->wordpress_logged_value;
        }

        if ($this->wordpress_sec_title && $this->wordpress_sec_value) {
            $cookies[$this->wordpress_sec_title] = $this->wordpress_sec_value;
        }

        return $cookies;
    }
}
