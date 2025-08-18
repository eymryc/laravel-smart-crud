# Laravel Smart CRUD Generator

<div align="center">

![Laravel Smart CRUD](https://img.shields.io/badge/Laravel-Smart%20CRUD-red?style=for-the-badge&logo=laravel)
![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue?style=for-the-badge&logo=php)
![Laravel Version](https://img.shields.io/badge/Laravel-10%2B-red?style=for-the-badge&logo=laravel)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

🚀 **Intelligent CRUD generator for Laravel that analyzes your database structure and generates complete, production-ready API resources.**

[Installation](#installation) • [Quick Start](#quick-start) • [Features](#features) • [Documentation](#documentation) • [Examples](#examples)

</div>

## ✨ Features

✅ **Intelligent Database Analysis** - Automatically analyzes your database schema  
✅ **Complete CRUD Generation** - Models, Controllers, Services, Repositories, DTOs  
✅ **API-First Design** - Standardized JSON API responses  
✅ **Repository Pattern** - Clean architecture with dependency injection  
✅ **Smart Validation** - Auto-generates validation rules based on database schema  
✅ **Search & Filtering** - Built-in search and sorting capabilities  
✅ **Exception Handling** - Proper error handling and custom exceptions  
✅ **Highly Configurable** - Customize everything via configuration file  
✅ **Type Safety** - Full PHP 8.1+ type declarations  
✅ **Best Practices** - Follows Laravel and PHP best practices

## 🎯 What Problem Does It Solve?

Building CRUD operations in Laravel is repetitive and time-consuming. You need to create:

- Models with relationships
- Controllers with proper validation
- Request classes with rules
- Resource classes for API responses
- Service layers for business logic
- Repository pattern for data access
- DTOs for type safety
- Exception handling
- Route registration

**Smart CRUD does all of this in 30 seconds** by analyzing your database structure!

## 📦 Installation

```bash
composer require rouangni/laravel-smart-crud
```

The package will be auto-discovered by Laravel. No additional setup required!

### Optional: Publish Configuration

```bash
php artisan vendor:publish --tag=smart-crud-config
```

### Optional: Publish Stubs for Customization

```bash
php artisan vendor:publish --tag=smart-crud-stubs
```

## 🚀 Quick Start

### 1. Create Your Migration

```php
// database/migrations/create_posts_table.php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->string('status')->default('draft');
    $table->boolean('is_published')->default(false);
    $table->timestamp('published_at')->nullable();
    $table->timestamps();
});
```

### 2. Generate Complete CRUD

```bash
php artisan make:smart-crud Post
```

### 3. That's It! 🎉

You now have a complete API with:

- Full CRUD operations
- Smart validation
- Search and filtering
- Proper error handling
- Standardized responses

## 📁 What Gets Generated

When you run `php artisan make:smart-crud Post`, you get:

```
app/
├── Http/
│   ├── Controllers/
│   │   └── PostController.php              # Complete API controller
│   ├── Requests/
│   │   ├── StorePostRequest.php           # Validation for creating
│   │   └── UpdatePostRequest.php          # Validation for updating
│   └── Resources/
│       ├── PostResource.php               # Single resource transformation
│       └── PostCollection.php             # Collection transformation
├── Services/
│   └── PostService.php                    # Business logic layer
├── Repositories/
│   ├── Contracts/
│   │   └── PostRepositoryInterface.php    # Repository contract
│   └── PostRepository.php                 # Data access layer
├── DTOs/
│   ├── CreatePostDTO.php                  # Type-safe create data
│   ├── UpdatePostDTO.php                  # Type-safe update data
│   └── PostFilterDTO.php                  # Type-safe filter data
└── Exceptions/
    └── PostNotFoundException.php          # Custom exception

Routes automatically registered in routes/api.php
```

## 🔥 API Endpoints

All endpoints are automatically registered:

| Method   | Endpoint          | Description                                       |
| -------- | ----------------- | ------------------------------------------------- |
| `GET`    | `/api/posts`      | List posts with filtering, search, and pagination |
| `POST`   | `/api/posts`      | Create a new post                                 |
| `GET`    | `/api/posts/{id}` | Get a specific post                               |
| `PUT`    | `/api/posts/{id}` | Update a post                                     |
| `DELETE` | `/api/posts/{id}` | Delete a post                                     |

## 📡 API Response Format

All responses follow a consistent, standardized format:

### Success Response

```json
{
  "success": true,
  "message": "Posts retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "title": "My First Post",
        "content": "This is the content...",
        "status": "published",
        "is_published": true,
        "published_at": "2025-01-15 10:30:00",
        "created_at": "2025-01-15 09:00:00",
        "updated_at": "2025-01-15 10:30:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 15,
      "total": 67,
      "from": 1,
      "to": 15
    }
  },
  "status": 200
}
```

### Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."],
    "content": ["The content field is required."]
  },
  "status": 422
}
```

## 🔍 Advanced Usage

### Filtering and Search

```bash
# Search in multiple fields
GET /api/posts?search=laravel

# Sort results
GET /api/posts?sort_by=created_at&sort_direction=desc

# Pagination
GET /api/posts?per_page=25

# Combine filters
GET /api/posts?search=php&sort_by=title&per_page=10&sort_direction=asc
```

### Command Options

```bash
# Force overwrite existing files
php artisan make:smart-crud Post --force

# Skip migration creation
php artisan make:smart-crud Post --no-migration

# Skip factory and seeder
php artisan make:smart-crud Post --no-factory --no-seeder

# Skip route registration
php artisan make:smart-crud Post --no-routes
```

## ⚙️ Configuration

The package is highly configurable. Publish the config file to customize:

```bash
php artisan vendor:publish --tag=smart-crud-config
```

### Key Configuration Options

```php
// config/smart-crud.php
return [
    'defaults' => [
        'pagination' => [
            'per_page' => 15,
            'max_per_page' => 100,
        ],
        'search' => [
            'fields' => ['name', 'title', 'description', 'email'],
        ],
    ],

    'api' => [
        'messages' => [
            'created' => 'Resource created successfully',
            'updated' => 'Resource updated successfully',
            // Customize all API messages
        ],
    ],

    'database' => [
        'searchable_columns' => [
            'name', 'title', 'description', 'content', 'email'
        ],
        'hidden_columns' => [
            'password', 'remember_token', 'api_token'
        ],
    ],
];
```

## 🧠 Smart Features

### Intelligent Type Detection

The package automatically detects column types and generates appropriate:

- **PHP Types**: `string`, `int`, `float`, `bool`, `Carbon`
- **Validation Rules**: `required`, `email`, `unique`, `integer`, `boolean`
- **Default Values**: Based on column nullability and type
- **Resource Formatting**: Dates formatted, passwords hidden

### Example Migration Analysis

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();                          // → int, not fillable
    $table->string('name');                // → string, required
    $table->string('email')->unique();     // → string, required, email, unique
    $table->decimal('salary', 10, 2);      // → float, numeric validation
    $table->boolean('is_active');          // → bool, boolean validation
    $table->timestamp('last_login')->nullable(); // → string, nullable
    $table->timestamps();                  // → not fillable, auto-formatted
});
```

**Generated Validation:**

```php
// StoreUserRequest
'name' => 'required|string|max:255',
'email' => 'required|email|unique:users,email',
'salary' => 'required|numeric',
'is_active' => 'required|boolean',
'last_login' => 'nullable|date',
```

## 🎨 Customization

### Custom Templates

Publish stubs to customize generated code:

```bash
php artisan vendor:publish --tag=smart-crud-stubs
```

Edit templates in `resources/stubs/smart-crud/`:

```php
// resources/stubs/smart-crud/controller.stub
<?php

namespace {{ controllerNamespace }};

// Your custom controller template...
```

### Repository Binding

Add to your `AppServiceProvider` or create a dedicated service provider:

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->bind(
        \App\Repositories\Contracts\PostRepositoryInterface::class,
        \App\Repositories\PostRepository::class
    );
}
```

## 🧪 Testing

The generated code is fully testable. Example:

```php
// tests/Feature/PostControllerTest.php
class PostControllerTest extends TestCase
{
    public function test_can_list_posts()
    {
        Post::factory()->count(3)->create();

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'data' => [
                             '*' => ['id', 'title', 'content']
                         ],
                         'pagination'
                     ]
                 ]);
    }

    public function test_can_create_post()
    {
        $data = [
            'title' => 'Test Post',
            'content' => 'Test content',
            'status' => 'draft'
        ];

        $response = $this->postJson('/api/posts', $data);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Resource created successfully'
                 ]);

        $this->assertDatabaseHas('posts', $data);
    }
}
```

## 🚨 Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- MySQL, PostgreSQL, SQLite, or SQL Server

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

```bash
git clone https://github.com/rouangni/laravel-smart-crud.git
cd laravel-smart-crud
composer install
composer test
```

## 📈 Performance

Smart CRUD generates optimized code:

- **Repository Pattern** for efficient database queries
- **Eager Loading** support built-in
- **Pagination** for large datasets
- **Caching** ready (via Laravel's cache system)
- **Index-friendly** search queries

## 🆚 Comparison

| Feature             | Manual CRUD | Laravel Breeze | Smart CRUD   |
| ------------------- | ----------- | -------------- | ------------ |
| Generation Time     | 2-4 hours   | 30 minutes     | 30 seconds   |
| Database Analysis   | Manual      | None           | Automatic    |
| Validation Rules    | Manual      | Basic          | Smart + Auto |
| Repository Pattern  | Manual      | None           | Included     |
| Type Safety (DTOs)  | Manual      | None           | Included     |
| API Standardization | Manual      | None           | Included     |
| Search & Filtering  | Manual      | None           | Included     |

## 📚 Examples

### E-commerce Product CRUD

```bash
php artisan make:smart-crud Product
```

With this migration:

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description');
    $table->decimal('price', 10, 2);
    $table->integer('stock');
    $table->string('sku')->unique();
    $table->boolean('is_active')->default(true);
    $table->foreignId('category_id')->constrained();
    $table->timestamps();
});
```

You instantly get:

- Product listing with search in name/description
- Price and stock validation
- SKU uniqueness validation
- Category relationship support
- Active/inactive filtering

### Blog Post Management

```bash
php artisan make:smart-crud Post
```

Perfect for content management with automatic:

- Title and content search
- Publication status filtering
- Date-based sorting
- SEO-friendly URLs support

## 🛠️ Troubleshooting

### Common Issues

**Issue**: "Table doesn't exist" error

```bash
# Solution: Run migration first
php artisan migrate
php artisan make:smart-crud Post --force
```

**Issue**: Repository binding not found

```php
// Solution: Add to AppServiceProvider
$this->app->bind(
    \App\Repositories\Contracts\PostRepositoryInterface::class,
    \App\Repositories\PostRepository::class
);
```

**Issue**: Validation not working as expected

```bash
# Solution: Clear cache and regenerate
php artisan config:clear
php artisan make:smart-crud Post --force
```

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Credits

- **Rouangni** - Creator and maintainer
- **Laravel Community** - For the amazing framework
- **Contributors** - Everyone who helps improve this package

## 📞 Support

- 🐛 **Bug Reports**: [GitHub Issues](https://github.com/rouangni/laravel-smart-crud/issues)
- 💡 **Feature Requests**: [GitHub Discussions](https://github.com/rouangni/laravel-smart-crud/discussions)
- 📖 **Documentation**: [Wiki](https://github.com/rouangni/laravel-smart-crud/wiki)

---

<div align="center">

**Made with ❤️ for the Laravel community**

[⭐ Star on GitHub](https://github.com/rouangni/laravel-smart-crud) • [🐦 Follow on Twitter](https://twitter.com/rouangni) • [📧 Email](mailto:contact@rouangni.com)

</div>
