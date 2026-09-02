#!/usr/bin/env bash
# ==============================================================================
# Production Deployment Script for Apolo (Ready eCommerce / POS) on Plesk
# Target Server: AlmaLinux 10.2 / Plesk (srv1199844.hstgr.cloud)
# ==============================================================================

set -e

echo "🚀 Starting Apolo Production Deployment..."

# 1. Check if .env exists
if [ ! -f .env ]; then
    echo "❌ Error: .env file not found in $(pwd)!"
    echo "Please create a .env file configured for your production database and domain."
    exit 1
fi

# 2. Install / Update Composer dependencies
echo "📦 Installing Composer dependencies (production mode)..."
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Generate App Key if missing
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating Application Key..."
    php artisan key:generate --force
fi

# 4. Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 5. Build Frontend Assets (Vite + Vue 3)
if command -v npm &> /dev/null; then
    echo "🎨 Building frontend assets with Vite..."
    npm install --no-audit --no-fund
    npm run build
else
    echo "⚠️ NPM not found. Make sure pre-built public/build assets are uploaded."
fi

# 6. Create Storage Symlink
echo "🔗 Linking public storage..."
php artisan storage:link || true

# 7. Optimize & Cache for Production
echo "⚡ Caching configurations, routes, and views..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. Fix Permissions for storage and cache
echo "🔒 Updating directory permissions..."
chmod -R 775 storage bootstrap/cache

echo "✅ Production deployment complete! Verify your application at your domain."
