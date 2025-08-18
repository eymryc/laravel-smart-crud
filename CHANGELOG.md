# Changelog

All notable changes to `laravel-smart-crud` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned

- Support for PostgreSQL, SQLite, and SQL Server
- GraphQL API generation option
- Relationship detection and generation
- OpenAPI/Swagger documentation generation
- Event sourcing support
- Multi-tenant architecture support

## [1.0.0] - 2025-01-18

### Added

- **Initial Release** 🎉
- Smart CRUD generation based on database analysis
- Complete MVC architecture generation (Models, Controllers, Services, Repositories)
- Repository pattern with interfaces and dependency injection
- DTO (Data Transfer Objects) classes for type safety
- Form Request classes with intelligent validation rules
- API Resource and Collection classes for response formatting
- Custom exception classes for proper error handling
- Standardized JSON API response format
- Search and filtering capabilities
- Pagination support
- Route auto-registration
- Highly configurable via config file
- Customizable code templates (stubs)
- Comprehensive documentation and examples

### Features

- **Database Analysis**: Automatically analyzes MySQL database structure
- **Type Detection**: Intelligent PHP type detection (string, int, float, bool)
- **Smart Validation**: Auto-generates validation rules based on column types and constraints
- **Security**: Automatic hiding of sensitive fields (passwords, tokens)
- **Performance**: Optimized queries with pagination and search
- **Standards**: Follows Laravel and PHP best practices
- **Flexibility**: Highly customizable templates and configuration

### Architecture

- **Repository Pattern**: Clean separation of data access logic
- **Service Layer**: Business logic encapsulation
- **DTO Pattern**: Type-safe data transfer
- **Exception Handling**: Proper error management
- **API-First**: Standardized JSON responses
- **SOLID Principles**: Well-structured, maintainable code

### Supported Features

- ✅ MySQL database analysis
- ✅ Complete CRUD operations (Create, Read, Update, Delete)
- ✅ Search functionality across multiple fields
- ✅ Sorting and filtering
- ✅ Pagination with metadata
- ✅ Validation with custom rules
- ✅ Exception handling
- ✅ API resource transformation
- ✅ Repository pattern implementation
- ✅ Service layer architecture
- ✅ Type-safe DTOs
- ✅ Route auto-registration
- ✅ Customizable templates
- ✅ Configuration management

### Command Options

- `--force`: Overwrite existing files
- `--no-migration`: Skip migration creation
- `--no-factory`: Skip factory creation
- `--no-seeder`: Skip seeder creation
- `--no-routes`: Skip route registration

### Configuration Options

- Pagination settings (per_page, max_per_page)
- Search field configuration
- API message customization
- Database column exclusion rules
- Template customization
- Route configuration

### Generated Files

For each resource (e.g., `Post`), the following files are generated:

- `app/Http/Controllers/PostController.php` - API controller with full CRUD
- `app/Http/Requests/StorePostRequest.php` - Validation for creating
- `app/Http/Requests/UpdatePostRequest.php` - Validation for updating
- `app/Http/Resources/PostResource.php` - Single resource transformation
- `app/Http/Resources/PostCollection.php` - Collection transformation
- `app/Services/PostService.php` - Business logic layer
- `app/Repositories/PostRepository.php` - Data access implementation
- `app/Repositories/Contracts/PostRepositoryInterface.php` - Repository contract
- `app/DTOs/CreatePostDTO.php` - Type-safe creation data
- `app/DTOs/UpdatePostDTO.php` - Type-safe update data
- `app/DTOs/PostFilterDTO.php` - Type-safe filter data
- `app/Exceptions/PostNotFoundException.php` - Custom exception

### API Endpoints Generated

- `GET /api/posts` - List resources with filtering and pagination
- `POST /api/posts` - Create new resource
- `GET /api/posts/{id}` - Get specific resource
- `PUT /api/posts/{id}` - Update resource
- `DELETE /api/posts/{id}` - Delete resource

### Requirements

- PHP 8.1 or higher
- Laravel 10.0, 11.0, or 12.0
- MySQL database (PostgreSQL, SQLite, SQL Server planned for future releases)

### Installation

```bash
composer require rouangni/laravel-smart-crud
```

### Basic Usage

```bash
# Generate complete CRUD for a model
php artisan make:smart-crud Post

# Generate with options
php artisan make:smart-crud Product --force --no-seeder
```

### Breaking Changes

- None (initial release)

### Deprecated

- None (initial release)

### Removed

- None (initial release)

### Fixed

- None (initial release)

### Security

- Automatic exclusion of sensitive fields (passwords, tokens, etc.)
- Proper validation and sanitization
- SQL injection prevention through Eloquent ORM
- Mass assignment protection

---

## Version History

### Pre-release Development

- **0.9.0** - Beta testing and refinement
- **0.8.0** - Template system implementation
- **0.7.0** - Configuration system development
- **0.6.0** - API response standardization
- **0.5.0** - Repository pattern implementation
- **0.4.0** - DTO system development
- **0.3.0** - Database analysis engine
- **0.2.0** - Code generation framework
- **0.1.0** - Initial concept and structure

---

## Contributing

We welcome contributions! Please read our [Contributing Guide](CONTRIBUTING.md) for details on:

- Bug reports
- Feature requests
- Pull requests
- Development setup
- Code standards

## Support

- 📖 [Documentation](README.md)
- 🐛 [Issues](https://github.com/rouangni/laravel-smart-crud/issues)
- 💬 [Discussions](https://github.com/rouangni/laravel-smart-crud/discussions)
- 📧 [Email Support](mailto:support@rouangni.com)

## License

This package is open-sourced software licensed under the [MIT License](LICENSE).
