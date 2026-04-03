# Dropzone

Self-hosted file sharing with one-time download links.

## Quick Start

```bash
# Clone and run
git clone https://github.com/k2sobot/dropzone.git
cd dropzone
docker-compose up -d
```

Visit: http://localhost:8080

**Admin Panel:** http://localhost:8080/admin  
**Default Password:** `admin123` (change in .env)

## Features

- Upload files and get shareable one-time download links
- Files auto-delete after download
- Orphaned files cleaned up after 7 days
- Admin panel with:
  - Dashboard with storage stats
  - Upload management (view/delete)
  - Background image customization
- Rate limiting (5 uploads/min per IP)
- Docker-ready with auto-migrations

## Configuration

Set these in `.env` or `docker-compose.yml`:

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_URL` | `http://localhost:8080` | Your domain |
| `ADMIN_PASSWORD` | `admin123` | Admin panel password |
| `APP_ENV` | `production` | `local` or `production` |
| `APP_DEBUG` | `false` | Enable debug mode |

## Docker Commands

```bash
# Build and start
docker-compose up -d --build

# View logs
docker-compose logs -f

# Reinstall dependencies
docker exec dropzone-app composer install

# Run artisan commands
docker exec dropzone-app php artisan migrate

# Check scheduler status
docker exec dropzone-app supervisorctl status
```

## How It Works

1. **Upload**: User selects file → stored in `storage/app/uploads/{uuid}/`
2. **Share**: System generates link: `https://yourdomain.com/d/{uuid}`
3. **Download**: Recipient clicks → file streamed → deleted
4. **Cleanup**: Scheduler runs hourly → removes expired files

## Architecture

```
├── app/
│   ├── Http/Controllers/
│   │   ├── UploadController.php      # Upload form
│   │   ├── DownloadController.php    # Download page
│   │   └── Admin/                    # Dashboard, Settings, etc.
│   ├── Models/
│   │   ├── Upload.php                # File metadata (UUID primary)
│   │   └── AdminSetting.php          # Key-value settings
│   └── Services/
│       └── FileService.php           # Upload/download/cleanup logic
├── docker/
│   ├── supervisord.conf              # Runs Apache + Scheduler
│   └── run-scheduler.sh              # Runs `php artisan schedule:run`
└── resources/views/
    ├── upload.blade.php              # Main layout
    ├── download.blade.php            # Download landing
    └── admin/                        # Dashboard, Uploads, Settings
```

## Security

- UUIDs are cryptographically random (128-bit)
- Admin password stored in environment (not database)
- Files stored outside web root
- Rate limiting prevents abuse

For production:
- Use HTTPS
- Set a strong `ADMIN_PASSWORD`
- Consider adding file virus scanning (ClamAV)
- Set `APP_DEBUG=false`

## Future Enhancements

- [ ] Password-protected links
- [ ] Custom expiration times
- [ ] Multiple downloads before delete
- [ ] Email notifications
- [ ] S3/DO Spaces storage
- [ ] User accounts with file history

## License

MIT