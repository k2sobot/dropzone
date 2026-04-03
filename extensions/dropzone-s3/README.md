# Dropzone S3 Extension

Store files on Amazon S3 or DigitalOcean Spaces.

## Installation

### Manual Install

```bash
# Copy to extensions directory
cp -r extensions/dropzone-s3 /var/www/extensions/

# Install AWS SDK
composer require aws/aws-sdk-php

# Enable in config/extensions.php
'extensions' => [
    'dropzone-s3' => true,
],
```

### Environment Variables

Add to your `.env`:

```env
# Enable S3 storage
S3_ENABLED=true

# AWS S3
S3_KEY=AKIAIOSFODNN7EXAMPLE
S3_SECRET=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
S3_BUCKET=my-dropzone-bucket
S3_REGION=us-east-1

# For DigitalOcean Spaces
S3_DO_SPACES=true
S3_ENDPOINT=https://nyc3.digitaloceanspaces.com
```

## Configuration

After enabling, visit Admin > Settings > Storage to configure.

## How It Works

- Files are uploaded directly to S3/Spaces
- Temporary URLs are generated for downloads (1 hour expiry)
- Files are deleted from S3 after download
- Reduced server load - files don't pass through your server

## Cost Considerations

- **S3**: ~$0.023/GB storage, ~$0.09/GB transfer
- **DO Spaces**: $5/mo for 250GB storage + 1TB transfer

## Troubleshooting

### Permission Errors

Ensure your S3 user has these permissions:
- `s3:PutObject`
- `s3:GetObject`
- `s3:DeleteObject`

### DO Spaces Connection

Set `S3_DO_SPACES=true` and provide the region endpoint:
```
S3_ENDPOINT=https://region.digitaloceanspaces.com
```
