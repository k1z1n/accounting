<?php

namespace App\Helpers;

use App\Models\Currency;
use Illuminate\Support\Collection;

class CurrencyHelper
{
    /**
     * Форматировать сумму для отображения
     */
    public static function formatAmount(float $amount, string $currencyCode = 'USD', int $decimals = 2): string
    {
        $currency = self::getCurrencyByCode($currencyCode);

        if (!$currency) {
            return number_format($amount, $decimals, '.', ' ') . ' ' . $currencyCode;
        }

        return number_format($amount, $decimals, '.', ' ') . ' ' . $currency->code;
    }

    /**
     * Получить валюту по коду
     */
    public static function getCurrencyByCode(string $code): ?Currency
    {
        return Currency::where('code', $code)->first();
    }

    /**
     * Получить все доступные валюты
     */
    public static function getAvailableCurrencies(): Collection
    {
        return Currency::orderBy('name')->get();
    }

    /**
     * Получить курсы валют (заглушка)
     */
    public static function getExchangeRates(): array
    {
        return [
            'USD' => 1.0,
            'EUR' => 0.85,
            'RUB' => 90.0,
            'BTC' => 0.000025,
            'ETH' => 0.00038,
        ];
    }

    /**
     * Конвертировать валюту
     */
    public static function convertCurrency(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $rates = self::getExchangeRates();

        if (!isset($rates[$fromCurrency]) || !isset($rates[$toCurrency])) {
            throw new \InvalidArgumentException('Неподдерживаемая валюта');
        }

        // Конвертируем через USD как базовую валюту
        $usdAmount = $amount / $rates[$fromCurrency];
        return $usdAmount * $rates[$toCurrency];
    }

    /**
     * Получить символ валюты
     */
    public static function getCurrencySymbol(string $code): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'RUB' => '₽',
            'BTC' => '₿',
            'ETH' => 'Ξ',
            'USDT' => '₮',
        ];

        return $symbols[$code] ?? $code;
    }

    /**
     * Проверить, является ли валюта криптовалютой
     */
    public static function isCryptoCurrency(string $code): bool
    {
        $cryptoCurrencies = [
            'BTC', 'ETH', 'USDT', 'BNB', 'ADA', 'XRP', 'SOL', 'DOT', 'DOGE', 'AVAX',
            'SHIB', 'MATIC', 'CRO', 'ATOM', 'LTC', 'LINK', 'UNI', 'BCH', 'XLM',
        ];

        return in_array(strtoupper($code), $cryptoCurrencies);
    }

    /**
     * Получить URL иконки валюты
     */
    public static function getCurrencyIconUrl(string $code): string
    {
        $iconPath = "/images/coins/{$code}.svg";

        if (file_exists(public_path($iconPath))) {
            return asset($iconPath);
        }

        // Возвращаем заглушку, если иконка не найдена
        return asset('/images/coins/default.svg');
    }

    /**
     * Валидировать номер кошелька
     */
    public static function validateWalletAddress(string $address, string $currencyCode): bool
    {
        if (self::isCryptoCurrency($currencyCode)) {
            return self::validateCryptoAddress($address, $currencyCode);
        }

        // Для фиатных валют - простая проверка
        return strlen($address) >= 10 && strlen($address) <= 50;
    }

    /**
     * Валидировать криптоадрес
     */
    private static function validateCryptoAddress(string $address, string $currencyCode): bool
    {
        return match (strtoupper($currencyCode)) {
            'BTC' => preg_match('/^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$|^bc1[a-z0-9]{39,59}$/', $address),
            'ETH', 'USDT' => preg_match('/^0x[a-fA-F0-9]{40}$/', $address),
            'LTC' => preg_match('/^[LM3][a-km-zA-HJ-NP-Z1-9]{26,33}$/', $address),
            default => strlen($address) >= 20 && strlen($address) <= 100,
        };
    }
}
