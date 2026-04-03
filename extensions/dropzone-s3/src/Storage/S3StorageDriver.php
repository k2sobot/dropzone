<?php

namespace Dropzone\S3;

use App\Services\StorageDriverInterface;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;

class S3StorageDriver implements StorageDriverInterface
{
    protected S3Client $client;
    protected string $bucket;
    protected string $region;
    protected bool $isDoSpaces;

    public function __construct()
    {
        $this->bucket = config( 'dropzone-s3.bucket' );
        $this->region = config( 'dropzone-s3.region', 'us-east-1' );
        $this->isDoSpaces = config( 'dropzone-s3.do_spaces', false );

        $config = [
            'version'     => 'latest',
            'region'      => $this->region,
            'credentials' => [
                'key'    => config( 'dropzone-s3.key' ),
                'secret' => config( 'dropzone-s3.secret' ),
            ],
        ];

        // DigitalOcean Spaces uses a different endpoint
        if ( $this->isDoSpaces ) {
            $config['endpoint'] = config(
                'dropzone-s3.endpoint',
                "https://{$this->region}.digitaloceanspaces.com"
            );
            $config['use_path_style_endpoint'] = true;
        }

        $this->client = new S3Client( $config );
    }

    /**
     * Store a file and return the path.
     */
    public function store( string $path, $contents, array $options = [] ): string
    {
        $acl = $options['acl'] ?? 'private';

        $this->client->putObject( [
            'Bucket' => $this->bucket,
            'Key'    => $path,
            'Body'   => $contents,
            'ACL'    => $acl,
        ] );

        return $path;
    }

    /**
     * Retrieve a file's contents.
     */
    public function get( string $path ): ?string
    {
        try {
            $result = $this->client->getObject( [
                'Bucket' => $this->bucket,
                'Key'    => $path,
            ] );

            return (string) $result['Body'];
        } catch ( \Exception $e ) {
            return null;
        }
    }

    /**
     * Delete a file.
     */
    public function delete( string $path ): bool
    {
        try {
            $this->client->deleteObject( [
                'Bucket' => $this->bucket,
                'Key'    => $path,
            ] );

            return true;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Check if a file exists.
     */
    public function exists( string $path ): bool
    {
        return $this->client->doesObjectExist( $this->bucket, $path );
    }

    /**
     * Get a temporary download URL.
     */
    public function temporaryUrl( string $path, int $expires = 3600 ): ?string
    {
        $cmd = $this->client->getCommand( 'GetObject', [
            'Bucket' => $this->bucket,
            'Key'    => $path,
        ] );

        $request = $this->client->createPresignedRequest( $cmd, "+{$expires} seconds" );

        return (string) $request->getUri();
    }

    /**
     * Get the driver name.
     */
    public function getName(): string
    {
        return $this->isDoSpaces ? 'DigitalOcean Spaces' : 'Amazon S3';
    }

    /**
     * Get configuration fields for admin UI.
     */
    public function getConfigFields(): array
    {
        return [
            [
                'name'        => 'key',
                'label'       => 'Access Key',
                'type'        => 'text',
                'required'    => true,
                'placeholder' => 'AKIA...',
            ],
            [
                'name'        => 'secret',
                'label'       => 'Secret Key',
                'type'        => 'password',
                'required'    => true,
            ],
            [
                'name'     => 'bucket',
                'label'    => 'Bucket Name',
                'type'     => 'text',
                'required' => true,
            ],
            [
                'name'        => 'region',
                'label'       => 'Region',
                'type'        => 'text',
                'default'     => 'us-east-1',
                'placeholder' => 'us-east-1',
            ],
            [
                'name'     => 'do_spaces',
                'label'    => 'DigitalOcean Spaces',
                'type'     => 'checkbox',
                'help'     => 'Enable if using DigitalOcean Spaces instead of AWS S3',
            ],
            [
                'name'        => 'endpoint',
                'label'       => 'Custom Endpoint',
                'type'        => 'text',
                'help'        => 'For DO Spaces: https://region.digitaloceanspaces.com',
                'conditional' => 'do_spaces',
            ],
        ];
    }
}
