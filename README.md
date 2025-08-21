# Laravel Smart CRUD

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rouangni/laravel-smart-crud.svg?style=flat-square)](https://packagist.org/packages/rouangni/laravel-smart-crud)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/rouangni/laravel-smart-crud.svg?style=flat-square)](https://packagist.org/packages/rouangni/laravel-smart-crud)

Un package Laravel moderne pour générer automatiquement des opérations CRUD complètes avec séparation API/Web et organisation par espaces de noms.

## ✨ Fonctionnalités

- 🚀 **Génération rapide** de CRUD complets
- 🔄 **Séparation API/Web** avec espaces de noms dédiés
- 📁 **Organisation par entité** - chaque modèle dans son propre dossier
- 🎯 **Architecture moderne** avec Services, Repositories, DTOs
- 🔧 **Configurable** - personnalisez stubs et configurations
- 📊 **Support du versioning API** (V1, V2, etc.)
- 🎨 **Templates personnalisables** via publication des stubs

## 📦 Installation

```bash
composer require rouangni/laravel-smart-crud
```

### Publication des configurations (optionnel)

```bash
# Publier la configuration
php artisan vendor:publish --tag=smart-crud-config

# Publier les stubs pour personnalisation
php artisan vendor:publish --tag=smart-crud-stubs
```

## 🚀 Utilisation

### Commande de base

```bash
php artisan make:smart-crud {Model} [options]
```

### Options disponibles

| Option | Description |
|--------|-------------|
| `--api` | Génère les fichiers API |
| `--web` | Génère les fichiers Web |
| `--version=V1` | Spécifie la version API (défaut: V1) |
| `--force` | Écrase les fichiers existants |
| `--skip-common` | Ignore les fichiers communs (Service, Repository, etc.) |
| `--skip-routes` | Ignore la génération des routes |
| `--skip-views` | Ignore la génération des vues (Web uniquement) |
| `--dry-run` | Affiche ce qui sera généré sans créer les fichiers |

### Exemples d'utilisation

```bash
# Génération API uniquement
php artisan make:smart-crud Product --api

# Génération Web uniquement  
php artisan make:smart-crud Product --web

# Génération API + Web
php artisan make:smart-crud Product --api --web

# Avec version API spécifique
php artisan make:smart-crud Product --api --version=V2

# Prévisualisation sans génération
php artisan make:smart-crud Product --api --dry-run

# Forcer la régénération
php artisan make:smart-crud Product --api --force

# Ignorer les fichiers communs (si déjà existants)
php artisan make:smart-crud Product --api --skip-common
```

## 📁 Structure générée

### Pour `Product` avec `--api`

```
App/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── V1/
│   │           └── Product/
│   │               └── ProductController.php
│   ├── Requests/
│   │   └── Api/
│   │       └── V1/
│   │           └── Product/
│   │               ├── StoreProductRequest.php
│   │               └── UpdateProductRequest.php
│   └── Resources/
│       └── Api/
│           └── V1/
│               └── Product/
│                   ├── ProductResource.php
│                   └── ProductCollection.php
├── Services/
│   └── Product/
│       └── ProductService.php
├── Repositories/
│   └── Product/
│       ├── ProductRepository.php
│       └── Contracts/
│           └── ProductRepositoryInterface.php
├── DTOs/
│   └── Product/
│       ├── ProductCreateDTO.php
│       ├── ProductUpdateDTO.php
│       └── ProductFilterDTO.php
└── Exceptions/
    └── Product/
        └── ProductException.php
```

### Pour `Product` avec `--web`

```
App/
├── Http/
│   ├── Controllers/
│   │   └── Web/
│   │       └── Product/
│   │           └── ProductController.php
│   └── Requests/
│       └── Web/
│           └── Product/
│               ├── StoreProductRequest.php
│               └── UpdateProductRequest.php
└── resources/
    └── views/
        └── products/
            ├── index.blade.php
            ├── create.blade.php
            ├── edit.blade.php
            └── show.blade.php
```

## 🎯 Fonctionnalités détaillées

### 1. **Contrôleurs intelligents**

Les contrôleurs générés incluent :
- ✅ Gestion d'erreurs complète
- ✅ Injection de dépendances
- ✅ Responses API standardisées
- ✅ Validation automatique
- ✅ Documentation inline

### 2. **Architecture en couches**

```
Controller → Service → Repository → Model
     ↓         ↓         ↓
   Request   DTO     Interface
```

### 3. **DTOs (Data Transfer Objects)**

- **CreateDTO** : Validation et transformation des données de création
- **UpdateDTO** : Validation et transformation des données de modification  
- **FilterDTO** : Gestion des filtres de recherche et pagination

### 4. **Traits utiles inclus**

#### ApiResponseTrait
```php
// Responses standardisées
$this->successResponse($data, 'Message');
$this->errorResponse('Error', 400);
$this->createdResponse($data);
$this->notFoundResponse();
```

#### BaseRepositoryTrait
```php
// Méthodes de base pour tous les repositories
$repository->all($filters, $relations);
$repository->paginate(15, $filters);
$repository->findBy('status', 'active');
```

## ⚙️ Configuration

Le fichier de configuration `config/smart-crud.php` permet de personnaliser :

```php
return [
    // Version API par défaut
    'default_api_version' => 'V1',
    
    // Espaces de noms
    'namespaces' => [
        'controllers' => [
            'api' => 'App\\Http\\Controllers\\Api',
            'web' => 'App\\Http\\Controllers\\Web',
        ],
        // ...
    ],
    
    // Chemins de génération
    'paths' => [
        'controllers' => [
            'api' => 'Http/Controllers/Api',
            'web' => 'Http/Controllers/Web',
        ],
        // ...
    ],
    
    // Configuration des stubs
    'stubs' => [
        'api' => [
            'controller' => 'Api/controller.api.stub',
            // ...
        ],
    ],
];
```

## 🎨 Personnalisation des templates

1. **Publier les stubs** :
```bash
php artisan vendor:publish --tag=smart-crud-stubs
```

2. **Modifier les templates** dans `resources/stubs/smart-crud/`

3. **Variables disponibles dans les stubs** :
- `{{ model }}` - Nom du modèle (ex: Product)
- `{{ modelVariable }}` - Variable camelCase (ex: product)
- `{{ modelPlural }}` - Nom pluriel (ex: Products)
- `{{ modelKebab }}` - Format kebab-case (ex: product)
- `{{ namespace }}` - Namespace de la classe
- `{{ class }}` - Nom de la classe

## 🔌 Intégration dans vos projets

### 1. Enregistrer les routes

Dans `app/Providers/RouteServiceProvider.php` :

```php
public function boot()
{
    // Routes API
    Route::prefix('api')
        ->middleware('api')
        ->group(function () {
            // Inclure vos routes API générées
            $this->loadRoutesFrom(base_path('routes/api/v1/product.php'));
        });

    // Routes Web
    Route::middleware('web')
        ->group(function () {
            // Inclure vos routes Web générées
            $this->loadRoutesFrom(base_path('routes/web/product.php'));
        });
}
```

### 2. Lier les interfaces aux implémentations

Dans `app/Providers/AppServiceProvider.php` :

```php
public function register()
{
    // Lier les repository interfaces
    $this->app->bind(
        \App\Repositories\Product\Contracts\ProductRepositoryInterface::class,
        \App\Repositories\Product\ProductRepository::class
    );
}
```

### 3. Configurer les models

Assurez-vous que vos models utilisent les traits appropriés :

```php
use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        // ... autres champs
    ];

    protected $casts = [
        'price' => 'decimal:2',
        // ... autres casts
    ];
}
```

## 🚦 Endpoints générés

### API Endpoints (exemple pour Product)

```
GET    /api/v1/products         # Liste des produits
POST   /api/v1/products         # Créer un produit
GET    /api/v1/products/{id}    # Afficher un produit
PUT    /api/v1/products/{id}    # Modifier un produit
DELETE /api/v1/products/{id}    # Supprimer un produit
```

### Web Routes (exemple pour Product)

```
GET    /products                # Liste des produits
GET    /products/create         # Formulaire de création
POST   /products                # Créer un produit
GET    /products/{id}           # Afficher un produit
GET    /products/{id}/edit      # Formulaire d'édition
PUT    /products/{id}           # Modifier un produit
DELETE /products/{id}           # Supprimer un produit
```

## 📝 Exemples d'usage

### Contrôleur API généré

```php
<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Services\Product\ProductService;
use Rouangni\SmartCrud\Traits\ApiResponseTrait;

class ProductController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filterDTO = new ProductFilterDTO($request->all());
        $products = $this->productService->getAll($filterDTO);

        return $this->successResponse(
            new ProductCollection($products),
            'Products retrieved successfully'
        );
    }

    // ... autres méthodes
}
```

### Service généré

```php
<?php

namespace App\Services\Product;

use App\Repositories\Product\Contracts\ProductRepositoryInterface;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function getAll(ProductFilterDTO $filterDTO)
    {
        $filters = $filterDTO->toArray();

        if ($filterDTO->paginate) {
            return $this->productRepository->paginate(
                $filterDTO->perPage ?? 15,
                $filters
            );
        }

        return $this->productRepository->all($filters);
    }

    // ... autres méthodes
}
```

## 🧪 Tests

Le package inclut des tests complets :

```bash
# Lancer les tests
composer test

# Tests avec couverture
composer test-coverage
```

## 🤝 Contribution

Les contributions sont les bienvenues ! Veuillez lire [CONTRIBUTING.md](CONTRIBUTING.md) pour les détails.

### Développement local

```bash
# Cloner le repository
git clone https://github.com/rouangni/laravel-smart-crud.git

# Installer les dépendances
composer install

# Lancer les tests
composer test
```

## 📄 License

Ce package est open-source sous [licence MIT](LICENSE.md).

## 🔄 Changelog

Voir [CHANGELOG.md](CHANGELOG.md) pour l'historique des versions.

## 💡 Roadmap

- [ ] Support des relations automatiques
- [ ] Génération de tests automatiques
- [ ] Interface graphique pour la configuration
- [ ] Support des APIs GraphQL
- [ ] Templates pour différents frameworks CSS

## 🆘 Support

- 📚 [Documentation complète](https://github.com/rouangni/laravel-smart-crud/wiki)
- 🐛 [Signaler un bug](https://github.com/rouangni/laravel-smart-crud/issues)
- 💬 [Discussions](https://github.com/rouangni/laravel-smart-crud/discussions)

---

Fait avec ❤️ pour la communauté Laravel