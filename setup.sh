#!/bin/bash

set -e

echo "🚀 Dropzone Setup"
echo "=================="

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.production .env
fi

# Install dependencies
echo "📦 Installing dependencies..."
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
echo "✅ Setup complete!"
echo ""
echo "🌐 Upload page: http://localhost:8080"
echo "🔐 Admin panel: http://localhost:8080/admin"
echo "⚙️  Admin password: check ADMIN_PASSWORD in .env"
echo ""