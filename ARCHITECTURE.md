# Dropzone Architecture

## Overview

Dropzone is a self-hosted file sharing platform where users upload files and receive one-time download links. Files are automatically deleted after download or after 7 days of inactivity.

## Core Entities

### Upload
- `id` - UUID (for shareable URLs)
- `filename` - Original filename
- `path` - Storage path
- `size` - File size in bytes
- `mime_type` - MIME type
- `downloaded_at` - Timestamp when downloaded (null until downloaded)
- `expires_at` - 7 days from upload
- `created_at`
- `deleted_at` - Soft delete for cleanup

### AdminSetting
- `key` - Setting name
- `value` - Setting value (JSON for complex settings)

### Key Settings
- `background_image` - Path to download page background
- `site_name` - Customizable site name
- `max_file_size` - Maximum upload size (default 100MB)

## User Flows

### Upload Flow
1. User visits homepage
2. Selects file(s) to upload
3. System generates UUID
4. File stored in `storage/app/uploads/{uuid}`
5. Database record created
6. User receives shareable link: `https://yourdomain.com/d/{uuid}`

### Download Flow
1. Recipient visits download link
2. System validates UUID exists and not expired
3. Landing page shows file info + custom background
4. Recipient clicks download
5. File streamed to browser
6. File deleted from storage
7. Database marked as downloaded

### Cleanup Flow (Scheduled Task - Hourly)
1. Find all uploads where `expires_at < now` AND `downloaded_at IS NULL`
2. Delete files from storage
3. Soft delete database records
4. Find all uploads where `downloaded_at IS NOT NULL` AND older than 1 hour
5. Permanently delete these records

## Security Considerations

- UUIDs are cryptographically random (not guessable)
- Rate limiting on uploads (per IP)
- Virus scanning via ClamAV (optional)
- No authentication required for basic use
- Admin panel requires authentication

## API Endpoints

### Public
- `GET /` - Upload page
- `POST /upload` - Handle file upload
- `GET /d/{uuid}` - Download landing page
- `GET /d/{uuid}/download` - Actual download

### Admin (Auth Required)
- `GET /admin` - Admin dashboard
- `GET /admin/uploads` - List all uploads
- `DELETE /admin/uploads/{id}` - Delete upload
- `GET /admin/settings` - Settings page
- `POST /admin/settings` - Update settings
- `POST /admin/settings/background` - Upload background image

## File Structure

```
app/
├── Http/Controllers/
│   ├── UploadController.php
│   └── Admin/
│       ├── DashboardController.php
│       ├── UploadController.php
│       └── SettingController.php
├── Models/
│   ├── Upload.php
│   └── AdminSetting.php
├── Services/
│   └── FileService.php
└── Console/Commands/
    └── CleanupExpiredUploads.php

database/migrations/
├── create_uploads_table.php
└── create_admin_settings_table.php

resources/views/
├── upload.blade.php
├── download.blade.php
└── admin/
    ├── layout.blade.php
    ├── dashboard.blade.php
    ├── uploads.blade.php
    └── settings.blade.php
```

## Future Enhancements

- Password-protected links
- Custom expiration times
- Multiple downloads (N downloads before delete)
- Email notifications
- Analytics dashboard
- S3/DO Spaces integration
- User accounts with file history