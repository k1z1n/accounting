# Архитектура приложения

## Обзор

Приложение реализовано с использованием принципов чистой архитектуры и паттернов проектирования для обеспечения масштабируемости, тестируемости и сопровождаемости кода.

## Структура проекта

```
app/
├── Contracts/           # Интерфейсы (контракты)
│   ├── Repositories/    # Интерфейсы репозиториев
│   └── Services/        # Интерфейсы сервисов
├── DTOs/               # Data Transfer Objects
├── Repositories/       # Реализации репозиториев
├── Services/           # Бизнес-логика
├── Http/
│   ├── Controllers/    # Контроллеры (тонкий слой)
│   ├── Requests/       # Form Request классы
│   └── Middleware/     # Middleware
├── Models/             # Eloquent модели
├── Providers/          # Service Providers
└── Exceptions/         # Пользовательские исключения
```

## Архитектурные слои

### 1. Контроллеры (Controllers)
**Назначение**: Тонкий слой, отвечающий за HTTP запросы/ответы
- Валидация входящих данных
- Вызов соответствующих сервисов
- Формирование HTTP ответов
- НЕ содержат бизнес-логику

**Принципы**:
- Один контроллер = одна сущность
- Максимум 10-15 строк в методе
- Только вызовы сервисов

### 2. Сервисы (Services)
**Назначение**: Инкапсуляция бизнес-логики
- Координация работы репозиториев
- Обработка бизнес-правил
- Валидация данных через DTO
- Логирование операций

**Примеры**:
- `AuthService` - аутентификация и авторизация
- `ApplicationService` - управление заявками
- `StatisticsService` - аналитика и отчеты
- `PlatformService` - управление платформами

### 3. Репозитории (Repositories)
**Назначение**: Абстракция над источниками данных
- Скрывают детали работы с БД
- Предоставляют единообразный интерфейс
- Могут кешировать данные
- Легко тестируются и заменяются

**Структура**:
```php
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByLogin(string $login): ?User;
    public function getRoleStatistics(): array;
}

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    // Реализация методов
}
```

### 4. DTO (Data Transfer Objects)
**Назначение**: Передача данных между слоями
- Валидация данных
- Типизация параметров
- Преобразование форматов
- Инкапсуляция логики обработки данных

**Пример**:
```php
class ApplicationDTO extends BaseDTO
{
    public ?int $id = null;
    public ?string $app_id = null;
    
    public function validate(): bool
    {
        return !empty($this->app_id);
    }
}
```

### 5. Модели (Models)
**Назначение**: Представление данных и отношений
- Только определение структуры
- Отношения между сущностями
- Аксессоры и мутаторы
- НЕ содержат бизнес-логику

## Принципы архитектуры

### 1. Dependency Injection
Все зависимости внедряются через конструктор:
```php
class ApplicationService implements ApplicationServiceInterface
{
    public function __construct(
        private ApplicationRepositoryInterface $applicationRepository,
        private CurrencyRepositoryInterface $currencyRepository
    ) {}
}
```

### 2. Interface Segregation
Интерфейсы разделены по функциональности:
- `BaseRepositoryInterface` - базовые CRUD операции
- `UserRepositoryInterface` - специфичные для пользователей методы

### 3. Single Responsibility
Каждый класс имеет одну ответственность:
- Контроллеры - HTTP слой
- Сервисы - бизнес-логика
- Репозитории - доступ к данным

### 4. Open/Closed Principle
Код открыт для расширения, закрыт для изменения:
- Новые функции добавляются через новые классы
- Существующий код не модифицируется

## Преимущества архитектуры

### 1. Тестируемость
- Все зависимости могут быть замоканы
- Каждый слой тестируется изолированно
- Легко писать unit и integration тесты

### 2. Масштабируемость
- Новые функции добавляются без изменения существующего кода
- Четкое разделение ответственности
- Возможность горизонтального масштабирования

### 3. Сопровождаемость
- Понятная структура проекта
- Код легко читается и понимается
- Изменения локализованы в конкретных слоях

### 4. Гибкость
- Легко заменить источник данных (БД, API, файлы)
- Можно менять бизнес-логику без затрагивания других слоев
- Поддержка различных форматов ввода/вывода

## Паттерны проектирования

### 1. Repository Pattern
Абстрагирует доступ к данным:
```php
// Вместо прямого обращения к модели
$users = User::where('role', 'admin')->get();

// Используем репозиторий
$users = $this->userRepository->findByRole('admin');
```

### 2. Service Layer Pattern
Инкапсулирует бизнес-логику:
```php
// Контроллер делегирует работу сервису
public function login(LoginRequest $request)
{
    $user = $this->authService->authenticate(
        $request->login,
        $request->password,
        $request->remember
    );
    
    return redirect()->route('dashboard');
}
```

### 3. Data Transfer Object Pattern
Передача типизированных данных:
```php
$applicationDTO = ApplicationDTO::fromArray($request->validated());
$application = $this->applicationService->createApplication($applicationDTO);
```

### 4. Dependency Injection Pattern
Внедрение зависимостей через DI контейнер Laravel:
```php
// Регистрация в ServiceProvider
$this->app->bind(UserRepositoryInterface::class, UserRepository::class);

// Автоматическое внедрение в конструктор
public function __construct(UserRepositoryInterface $userRepository)
{
    $this->userRepository = $userRepository;
}
```

## Примеры использования

### Создание новой функциональности

1. **Создать интерфейс репозитория**:
```php
interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function findByStatus(string $status): Collection;
}
```

2. **Реализовать репозиторий**:
```php
class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function findByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }
}
```

3. **Создать DTO**:
```php
class OrderDTO extends BaseDTO
{
    public ?string $status = null;
    public ?float $amount = null;
    
    public function validate(): bool
    {
        return !empty($this->status) && $this->amount > 0;
    }
}
```

4. **Создать сервис**:
```php
class OrderService implements OrderServiceInterface
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {}
    
    public function createOrder(array $data): Order
    {
        $orderDTO = OrderDTO::fromArray($data);
        
        if (!$orderDTO->validate()) {
            throw new InvalidArgumentException('Некорректные данные заказа');
        }
        
        return $this->orderRepository->create($orderDTO->getModelData());
    }
}
```

5. **Зарегистрировать в ServiceProvider**:
```php
$this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
$this->app->bind(OrderServiceInterface::class, OrderService::class);
```

6. **Использовать в контроллере**:
```php
class OrderController extends Controller
{
    public function __construct(
        private OrderServiceInterface $orderService
    ) {}
    
    public function store(OrderRequest $request)
    {
        $order = $this->orderService->createOrder($request->validated());
        return response()->json($order, 201);
    }
}
```

## Лучшие практики

### 1. Именование
- Репозитории: `EntityRepository`, `EntityRepositoryInterface`
- Сервисы: `EntityService`, `EntityServiceInterface`
- DTO: `EntityDTO`
- Контроллеры: `EntityController`

### 2. Обработка ошибок
- Используйте специфичные исключения
- Логируйте ошибки в сервисном слое
- Возвращайте понятные сообщения пользователю

### 3. Валидация
- Валидация ввода в Form Request классах
- Бизнес-валидация в DTO
- Валидация данных в сервисах

### 4. Тестирование
- Unit тесты для сервисов
- Integration тесты для репозиториев
- Feature тесты для контроллеров

## Миграция старого кода

При рефакторинге существующего кода:

1. Создайте интерфейсы для существующих классов
2. Перенесите бизнес-логику из контроллеров в сервисы
3. Замените прямые обращения к моделям на репозитории
4. Внедрите DTO для передачи данных
5. Покройте новый код тестами

Такая архитектура обеспечивает высокое качество кода, легкость сопровождения и возможность роста проекта. 
