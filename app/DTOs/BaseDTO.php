<?php

namespace App\DTOs;

abstract class BaseDTO
{
    /**
     * Создать DTO из массива данных
     */
    public static function fromArray(array $data): static
    {
        $instance = new static();

        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->{$key} = $value;
            }
        }

        return $instance;
    }

    /**
     * Преобразовать DTO в массив
     */
    public function toArray(): array
    {
        $result = [];
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $result[$property->getName()] = $property->getValue($this);
        }

        return $result;
    }

    /**
     * Преобразовать DTO в JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Валидация данных DTO
     */
    public function validate(): bool
    {
        // Базовая валидация - может быть переопределена в дочерних классах
        return true;
    }
}
