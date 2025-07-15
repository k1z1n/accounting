<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HasApiResponse
{
    /**
     * Успешный ответ
     */
    protected function successResponse(mixed $data = null, string $message = 'Успешно', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Ответ с ошибкой
     */
    protected function errorResponse(string $message = 'Ошибка', int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Ответ с данными пагинации
     */
    protected function paginatedResponse($paginator, string $message = 'Данные получены'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
        ]);
    }

    /**
     * Ответ для валидационных ошибок
     */
    protected function validationErrorResponse(array $errors, string $message = 'Ошибка валидации'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Ответ "не найдено"
     */
    protected function notFoundResponse(string $message = 'Ресурс не найден'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Ответ "не авторизован"
     */
    protected function unauthorizedResponse(string $message = 'Не авторизован'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Ответ "нет доступа"
     */
    protected function forbiddenResponse(string $message = 'Нет доступа'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }
}
