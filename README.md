# Caisse Pitch — POS Boîte de Nuit

Application de caisse complète pour gestion de boîte de nuit. Laravel 12 + Livewire + nwidart/laravel-modules.

## Prérequis

- PHP 8.2+
- Composer 2+
- MySQL 8+
- Node.js 18+ / npm
- wkhtmltopdf (pour export PDF Snappy)

## Installation

```bash
# 1. Cloner le dépôt
git clone <url> caisse-pitch
cd caisse-pitch

# 2. Copier et configurer l'environnement
cp .env.example .env
# Éditer .env : DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL

# 3. Installer les dépendances PHP
composer install

# 4. Générer la clé applicative
php artisan key:generate

# 5. Créer la base de données MySQL
# CREATE DATABASE caisse_pitch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 6. Lancer les migrations + seeders
php artisan migrate --seed

# 7. Installer les dépendances JS + compiler les assets
npm install && npm run build

# 8. Démarrer le serveur de développement
composer run dev
```

## Commandes utiles

```bash
# Lancer tous les services (serveur, queue, logs, vite)
composer run dev

# Migrations
php artisan migrate
php artisan migrate:fresh --seed

# Modules (nwidart)
php artisan module:list
php artisan module:make NomModule
php artisan module:migrate NomModule

# Tests
composer run test
```

## Structure des modules

```
Modules/
├── Users/          # Utilisateurs, rôles, permissions
├── Settings/       # Paramétrage général de l'établissement
├── Categories/     # Catégories de produits
├── Products/       # Produits, prix, marges
├── Suppliers/      # Fournisseurs
├── Purchases/      # Achats fournisseurs
├── Recipes/        # Cocktails et recettes (déduction stock)
├── Stock/          # Mouvements de stock, inventaires, alertes
├── CashRegisters/  # Caisses, sessions, ouverture/fermeture
├── Sales/          # Ventes, POS, paiements
├── Tickets/        # Tickets de caisse, impression thermique
├── Reports/        # Rapports vente et stock
└── Dashboard/      # Tableau de bord général
```

## Rôles disponibles

Administrateur, Gérant, Superviseur, Caissier, Barman, Serveur, Magasinier, Comptable, Lecteur

## PDF (Snappy)

Installer wkhtmltopdf : https://wkhtmltopdf.org/downloads.html

Configurer le chemin dans `config/snappy.php` :
```php
'binary' => env('WKHTMLTOPDF_BINARY', '/usr/local/bin/wkhtmltopdf'),
```
