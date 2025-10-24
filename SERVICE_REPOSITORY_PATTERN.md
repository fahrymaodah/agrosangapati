# Service Repository Pattern

Dokumentasi implementasi Service Repository Pattern untuk pengembangan aplikasi Agrosangapati.

## Arsitektur

```
┌─────────────┐
│   Client    │
└──────┬──────┘
       │ HTTP Request
       ▼
┌─────────────┐
│ Controller  │ ◄─── Handles HTTP requests/responses
└──────┬──────┘
       │ Calls business logic
       ▼
┌─────────────┐
│   Service   │ ◄─── Business logic & validation
└──────┬──────┘
       │ Calls data operations
       ▼
┌─────────────┐
│ Repository  │ ◄─── Database queries
└──────┬──────┘
       │ Uses Eloquent
       ▼
┌─────────────┐
│    Model    │ ◄─── Database representation
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  Database   │
└─────────────┘
```

## Struktur Folder

```
src/app/
├── Http/Controllers/          # Controller Layer
│   └── UserController.php
├── Services/                  # Service Layer (Business Logic)
│   └── UserService.php
├── Repositories/
│   ├── Contracts/            # Repository Interfaces
│   │   ├── BaseRepositoryInterface.php
│   │   └── UserRepositoryInterface.php
│   └── Eloquent/             # Repository Implementations
│       ├── BaseRepository.php
│       └── UserRepository.php
└── Providers/
    └── RepositoryServiceProvider.php
```

## Layer Responsibilities

### 1. Repository Layer
**Tanggung Jawab**: Query database, CRUD operations

**Contoh**: UserRepository
```php
// Hanya berisi query database
public function findByEmail(string $email): ?User
{
    return $this->model->where('email', $email)->first();
}

public function getActiveUsers(): Collection
{
    return $this->model->whereNotNull('email_verified_at')->get();
}
```

### 2. Service Layer
**Tanggung Jawab**: Business logic, validasi, transformasi data

**Contoh**: UserService
```php
// Berisi business logic seperti hash password, validasi, dll
public function createUser(array $data): User
{
    // Business logic: Hash password
    if (isset($data['password'])) {
        $data['password'] = Hash::make($data['password']);
    }
    
    // Bisa tambah logic lain: send email, logging, etc
    
    return $this->userRepository->create($data);
}
```

### 3. Controller Layer
**Tanggung Jawab**: Handle HTTP request/response, validasi input

**Contoh**: UserController
```php
// Handle HTTP dan delegasi ke service
public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ]);
    
    $user = $this->userService->createUser($validated);
    
    return response()->json(['data' => $user], 201);
}
```

## Cara Implementasi Entity Baru

### Step 1: Buat Repository Interface

Buat interface di `app/Repositories/Contracts/`:

```php
<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySku(string $sku);
    public function getActiveProducts(): Collection;
    public function searchByName(string $name): Collection;
    public function getProductsByCategory(int $categoryId): Collection;
}
```

### Step 2: Buat Repository Implementation

Buat implementasi di `app/Repositories/Eloquent/`:

```php
<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    protected function makeModel()
    {
        return new Product();
    }
    
    public function findBySku(string $sku)
    {
        return $this->model->where('sku', $sku)->first();
    }
    
    public function getActiveProducts(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }
    
    public function searchByName(string $name): Collection
    {
        return $this->model
            ->where('name', 'like', "%{$name}%")
            ->get();
    }
    
    public function getProductsByCategory(int $categoryId): Collection
    {
        return $this->model
            ->where('category_id', $categoryId)
            ->get();
    }
}
```

### Step 3: Buat Service

Buat service di `app/Services/`:

```php
<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    protected ProductRepositoryInterface $productRepository;
    
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    
    public function getAllProducts(): Collection
    {
        return $this->productRepository->all();
    }
    
    public function createProduct(array $data)
    {
        // Business logic: Generate SKU jika tidak ada
        if (!isset($data['sku'])) {
            $data['sku'] = $this->generateSku($data['name']);
        }
        
        // Business logic: Validasi harga
        if ($data['price'] < 0) {
            throw new \Exception('Price cannot be negative');
        }
        
        return $this->productRepository->create($data);
    }
    
    public function updateProduct(int $id, array $data): bool
    {
        // Business logic: Update timestamp
        $data['last_updated_by'] = auth()->id();
        
        return $this->productRepository->update($id, $data);
    }
    
    private function generateSku(string $name): string
    {
        // Business logic untuk generate SKU
        return strtoupper(substr($name, 0, 3)) . '-' . time();
    }
}
```

### Step 4: Register di Service Provider

Update `app/Providers/RepositoryServiceProvider.php`:

```php
public function register(): void
{
    // User Repository
    $this->app->bind(
        UserRepositoryInterface::class,
        UserRepository::class
    );
    
    // Product Repository - TAMBAHKAN INI
    $this->app->bind(
        ProductRepositoryInterface::class,
        ProductRepository::class
    );
}
```

### Step 5: Buat Controller

```bash
php artisan make:controller ProductController --resource
```

Update controller:

```php
<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected ProductService $productService;
    
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    
    public function index(): JsonResponse
    {
        $products = $this->productService->getAllProducts();
        return response()->json(['data' => $products]);
    }
    
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|unique:products',
        ]);
        
        $product = $this->productService->createProduct($validated);
        return response()->json(['data' => $product], 201);
    }
    
    // Tambahkan method lainnya...
}
```

### Step 6: Register Routes

Update `routes/api.php`:

```php
use App\Http\Controllers\ProductController;

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
});
```

## Base Repository Methods

Semua repository otomatis punya method ini dari `BaseRepository`:

```php
// Mendapatkan semua data
all($columns = ['*'], $relations = [])

// Cari berdasarkan ID
findById($id, $columns = ['*'], $relations = [])

// Cari berdasarkan kolom tertentu
findByColumn($column, $value, $columns = ['*'])

// Buat data baru
create(array $data)

// Update data
update($id, array $data)

// Hapus data
delete($id)

// Pagination
paginate($perPage = 15, $columns = ['*'])

// Cari dengan multiple kondisi
findWhere(array $conditions, $columns = ['*'])
```

## Best Practices

### 1. Repository: Hanya Query Database
✅ **DO**:
```php
public function findActiveUsers(): Collection
{
    return $this->model->where('is_active', true)->get();
}
```

❌ **DON'T**:
```php
public function findActiveUsers(): Collection
{
    // Jangan taruh business logic di repository
    $users = $this->model->where('is_active', true)->get();
    
    // Kirim email? NO! Ini business logic
    foreach ($users as $user) {
        Mail::to($user)->send(new WelcomeEmail());
    }
    
    return $users;
}
```

### 2. Service: Business Logic
✅ **DO**:
```php
public function registerUser(array $data): User
{
    // Hash password (business logic)
    $data['password'] = Hash::make($data['password']);
    
    // Generate token (business logic)
    $data['verification_token'] = Str::random(32);
    
    $user = $this->userRepository->create($data);
    
    // Send email (business logic)
    Mail::to($user)->send(new VerificationEmail($user));
    
    // Log activity (business logic)
    Log::info('New user registered', ['user_id' => $user->id]);
    
    return $user;
}
```

### 3. Controller: HTTP Handling
✅ **DO**:
```php
public function store(Request $request): JsonResponse
{
    // Validasi request
    $validated = $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users',
    ]);
    
    try {
        // Delegasi ke service
        $user = $this->userService->registerUser($validated);
        
        // Return HTTP response
        return response()->json([
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to create user',
            'error' => $e->getMessage()
        ], 500);
    }
}
```

## Dependency Injection

Semua dependency di-inject melalui constructor:

```php
// Service menerima Repository melalui interface
class ProductService
{
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }
}

// Controller menerima Service
class ProductController
{
    public function __construct(
        ProductService $productService
    ) {
        $this->productService = $productService;
    }
}
```

Binding interface ke implementation ada di `RepositoryServiceProvider`.

## Testing

Dengan pattern ini, testing menjadi mudah karena bisa mock dependencies:

```php
public function test_can_create_product()
{
    // Mock repository
    $mockRepo = Mockery::mock(ProductRepositoryInterface::class);
    $mockRepo->shouldReceive('create')
             ->once()
             ->andReturn(new Product(['id' => 1, 'name' => 'Test']));
    
    // Inject mock ke service
    $service = new ProductService($mockRepo);
    
    // Test
    $result = $service->createProduct(['name' => 'Test']);
    
    $this->assertEquals('Test', $result->name);
}
```

## Kapan Menggunakan Pattern Ini?

✅ **Gunakan jika**:
- Project besar dengan banyak business logic
- Butuh testability tinggi
- Tim development lebih dari 1 orang
- Maintenance jangka panjang

❌ **Tidak perlu jika**:
- Project sangat kecil (CRUD sederhana)
- Prototype cepat
- Solo development untuk personal project kecil

## Contoh Use Case Lengkap: User

Lihat implementasi lengkap di:
- `app/Repositories/Contracts/UserRepositoryInterface.php`
- `app/Repositories/Eloquent/UserRepository.php`
- `app/Services/UserService.php`
- `app/Http/Controllers/UserController.php`
- `routes/api.php`

Test API:
```bash
# Create user
curl -X POST http://agrosangapati.local/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@example.com","password":"password123","password_confirmation":"password123"}'

# Get all users
curl http://agrosangapati.local/api/users

# Get user by ID
curl http://agrosangapati.local/api/users/1

# Search users
curl "http://agrosangapati.local/api/users/search?q=Test"

# Update user
curl -X PUT http://agrosangapati.local/api/users/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Updated Name"}'

# Delete user
curl -X DELETE http://agrosangapati.local/api/users/1
```

## Summary

Pattern ini memberikan:
- ✅ Separation of Concerns
- ✅ Testability
- ✅ Maintainability
- ✅ Scalability
- ✅ Reusability

Ikuti struktur ini untuk semua entity baru yang akan dikembangkan.
