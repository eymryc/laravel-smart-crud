#!/bin/bash

# Script de test pour le package Laravel Smart CRUD
# Fonctionne sur macOS, Laravel 12, SQLite, package local

set -e  # Arrêt si erreur

echo "🚀 Test du package Laravel Smart CRUD"
echo "======================================"

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

log_info() { echo -e "${BLUE}ℹ️  $1${NC}"; }
log_success() { echo -e "${GREEN}✅ $1${NC}"; }
log_warning() { echo -e "${YELLOW}⚠️  $1${NC}"; }
log_error() { echo -e "${RED}❌ $1${NC}"; }

# Variables
PACKAGE_PATH="/Users/xselservices/Herd/laravel-smart-crud"
TEST_PROJECT_NAME="smart-crud-test"
TEST_PROJECT_PATH="../${TEST_PROJECT_NAME}"

log_info "Début des tests du package..."

# 1. Créer un projet Laravel 12
log_info "1. Création d'un projet Laravel de test..."
cd ..
if [ -d "$TEST_PROJECT_NAME" ]; then
    log_warning "Suppression de l'ancien projet de test..."
    rm -rf "$TEST_PROJECT_NAME"
fi

laravel new "$TEST_PROJECT_NAME"
cd "$TEST_PROJECT_NAME"
log_success "Projet Laravel créé"

# 2. Configurer la base de données SQLite
log_info "2. Configuration de la base de données..."
touch database/database.sqlite

# Modification du .env compatible macOS (BSD sed)
sed -i '' 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
sed -i '' 's/DB_DATABASE=.*/DB_DATABASE=database\/database.sqlite/' .env
sed -i '' '/DB_HOST/d' .env
sed -i '' '/DB_PORT/d' .env
sed -i '' '/DB_USERNAME/d' .env
sed -i '' '/DB_PASSWORD/d' .env
log_success "Base de données SQLite configurée"

# 3. Ajouter le package local
log_info "3. Ajout du package en local..."
composer config repositories.local-smart-crud path "$PACKAGE_PATH" --file composer.json
composer require rouangni/laravel-smart-crud:@dev --no-interaction
log_success "Package ajouté"

# 4. Créer un modèle de test
log_info "4. Création d'un modèle Product pour les tests..."
php artisan make:model Product -m
log_success "Modèle Product créé"

# 5. Configurer la migration
log_info "5. Configuration de la migration..."
MIGRATION_FILE=$(ls database/migrations/*_create_products_table.php)
cat > "$MIGRATION_FILE" << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('slug')->unique();
            $table->decimal('price', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
EOF
log_success "Migration Product configurée"

# 6. Configurer le modèle
log_info "6. Configuration du modèle Product..."
cat > app/Models/Product.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'description',
        'status',
        'slug',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];
}
EOF
log_success "Modèle Product configuré"

# 7. Lancer la migration
log_info "7. Exécution des migrations..."
php artisan migrate --no-interaction
log_success "Migrations exécutées"

# 8. Test de génération API
log_info "8. Test de génération API..."
php artisan make:smart-crud Product --api --dry-run
php artisan make:smart-crud Product --api --force
log_success "Génération API réussie"

# 9. Test de génération Web
log_info "9. Test de génération Web..."
php artisan make:smart-crud Product --web --dry-run
php artisan make:smart-crud Product --web --force
log_success "Génération Web réussie"

log_success "🎉 Tous les tests de génération sont passés!"

log_info "📁 Projet de test créé dans: $TEST_PROJECT_PATH"
log_info "🔍 Vous pouvez examiner les fichiers générés pour validation"

cd "$PACKAGE_PATH"
