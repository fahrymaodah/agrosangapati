# Backend Architecture Documentation

**Last Updated**: November 3, 2025

> Backend architecture patterns dan best practices untuk AgroSangapati project.

---

## 📚 Documentation Files

### 1. [Repository Pattern](./repository-pattern.md) 🗄️
**Contains**:
- Repository-Service-Controller architecture
- Interface contracts
- Eloquent implementation
- Query optimization
- Code examples

### 2. [Service Layer](./service-layer.md) ⚙️
**Contains**:
- Business logic organization
- Service responsibilities
- Transaction handling
- Error handling
- Validation patterns

### 3. [API Structure](./api-structure.md) 🔌
**Contains**:
- RESTful API design
- Response formatting
- Error responses
- Pagination
- Filtering & sorting

---

## 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────┐
│              HTTP Request                    │
└──────────────────┬──────────────────────────┘
                   ↓
         ┌─────────────────┐
         │   Controller    │  - HTTP handling
         │  (Thin layer)   │  - Request validation
         └────────┬────────┘  - Response formatting
                  ↓
         ┌─────────────────┐
         │    Service      │  - Business logic
         │ (Fat layer)     │  - Orchestration
         └────────┬────────┘  - Transactions
                  ↓
         ┌─────────────────┐
         │   Repository    │  - Data access
         │  (Data layer)   │  - Query building
         └────────┬────────┘  - Database operations
                  ↓
         ┌─────────────────┐
         │      Model      │  - Eloquent ORM
         │   (Entity)      │  - Relationships
         └─────────────────┘  - Accessors/Mutators
```

---

## 📋 Pattern Implementation

### Repository Pattern
**Purpose**: Abstraksi data access layer
```php
interface TransactionRepositoryInterface
{
    public function getAllByPoktan(int $poktanId);
    public function create(array $data): Transaction;
    public function findById(int $id): ?Transaction;
}

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function getAllByPoktan(int $poktanId)
    {
        return Transaction::where('poktan_id', $poktanId)
            ->with(['category', 'user'])
            ->latest()
            ->get();
    }
}
```

### Service Layer
**Purpose**: Business logic & orchestration
```php
class TransactionService
{
    public function __construct(
        private TransactionRepositoryInterface $repository,
        private CashBalanceService $cashBalanceService
    ) {}
    
    public function createTransaction(array $data): Transaction
    {
        DB::beginTransaction();
        try {
            // Validate balance
            if ($data['type'] === 'expense') {
                $this->validateBalance($data);
            }
            
            // Create transaction
            $transaction = $this->repository->create($data);
            
            // Update cash balance
            $this->cashBalanceService->updateFromTransaction($transaction);
            
            DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

### Controller
**Purpose**: HTTP handling only
```php
class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $service
    ) {}
    
    public function store(StoreTransactionRequest $request)
    {
        try {
            $transaction = $this->service->createTransaction(
                $request->validated()
            );
            
            return response()->json([
                'success' => true,
                'data' => $transaction,
                'message' => 'Transaction created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
```

---

## 🎯 Best Practices

### 1. Dependency Injection
```php
// ✅ Good - Use DI
public function __construct(
    private TransactionRepositoryInterface $repository
) {}

// ❌ Bad - Direct instantiation
public function __construct()
{
    $this->repository = new TransactionRepository();
}
```

### 2. Type Hinting
```php
// ✅ Good - Strict types
public function create(array $data): Transaction
{
    return $this->repository->create($data);
}

// ❌ Bad - No types
public function create($data)
{
    return $this->repository->create($data);
}
```

### 3. Transaction Management
```php
// ✅ Good - Wrap in transaction
DB::transaction(function () use ($data) {
    $transaction = $this->repository->create($data);
    $this->updateBalance($transaction);
    return $transaction;
});

// ❌ Bad - No transaction
$transaction = $this->repository->create($data);
$this->updateBalance($transaction);
```

### 4. Error Handling
```php
// ✅ Good - Specific exceptions
try {
    return $this->service->create($data);
} catch (InsufficientBalanceException $e) {
    return response()->json(['error' => $e->getMessage()], 400);
} catch (ValidationException $e) {
    return response()->json(['errors' => $e->errors()], 422);
}

// ❌ Bad - Generic catch
try {
    return $this->service->create($data);
} catch (\Exception $e) {
    return response()->json(['error' => 'Something went wrong'], 500);
}
```

---

## 📊 Current Implementation

### Completed Modules (32/45 tasks)
- ✅ Keuangan: 7 modules (100%)
- ✅ Hasil Bumi: 8 modules (100%)
- ✅ Pemasaran: 8 modules (100%)
- ✅ Auth: 3 modules (100%)
- ⏳ Additional: 3/4 modules (75%)

### Repository-Service Coverage
- 23 Repositories implemented
- 23 Services implemented
- 143+ API endpoints created

---

## 🔗 Related Documentation

- [Filament Resources](../filament/) - Frontend integration
- [Project Analysis](../../PROJECT_ANALYSIS.md) - Requirements
- [Task List](../../TASK_LIST.md) - Implementation progress

---

## 📞 Resources

- **Laravel Docs**: https://laravel.com/docs/architecture
- **Repository Pattern**: https://designpatternsphp.readthedocs.io
- **Clean Code**: Robert C. Martin

---

**Project**: AgroSangapati  
**Framework**: Laravel 11.x  
**Pattern**: Repository-Service-Controller
