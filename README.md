# Dropzone

Self-hosted file sharing with one-time download links.

## Features (MVP)

- Upload files and get shareable one-time download links
- Files auto-delete after first download
- Orphaned files cleaned up after 7 days
- Admin panel with:
  - Background image customization for download page
  - Manage/delete all download links
- Clean, simple landing page for recipients

## Tech Stack

- Laravel 11
- PHP 8.2+
- MySQL/PostgreSQL
- Redis (optional, for queues)

## Installation

### Docker (Recommended)

```bash
# Build and start
docker-compose up -d --build

# Run setup inside container
docker exec -it dropzone-app bash setup.sh

# Or manually:
docker exec -it dropzone-app composer install
docker exec -it dropzone-app php artisan key:generate
docker exec -it dropzone-app php artisan migrate
```

Visit: http://localhost:8080

### Manual

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
```

## Security

**Important:** Change the default admin password!

1. Set `ADMIN_PASSWORD` in your `.env` file
2. Use a strong, unique password

The admin panel is protected by this password. For production, consider:
- Adding rate limiting to the login form
- Using Laravel's built-in authentication
- Adding two-factor authentication

## License

MIT