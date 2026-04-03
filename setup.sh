#!/bin/bash

set -e

echo "🚀 Setting up Dropzone..."

# Copy environment file
if [ ! -f .env ]; then
    cp .env.production .env
    echo "✓ Created .env file"
fi

# Install dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# Generate app key
echo "🔑 Generating application key..."
php artisan key:generate --force

# Create database
echo "💾 Setting up database..."
touch storage/database.sqlite
chmod 666 storage/database.sqlite

# Run migrations
echo "📊 Running migrations..."
php artisan migrate --force

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache

echo ""
echo "✅ Dropzone is ready!"
echo "🌐 Visit: http://localhost:8080"
echo ""
echo "Admin panel: http://localhost:8080/admin"