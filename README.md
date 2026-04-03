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

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## License

MIT