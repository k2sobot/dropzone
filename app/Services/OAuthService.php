<?php

namespace App\Services;

use App\Models\OAuthProvider;
use App\Models\SystemLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OAuthService
{
    protected Client $httpClient;

    // OAuth provider configurations
    protected array $providers = [
        'google' => [
            'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'user_url' => 'https://www.googleapis.com/oauth2/v2/userinfo',
            'scope' => 'openid email profile',
        ],
        'github' => [
            'authorize_url' => 'https://github.com/login/oauth/authorize',
            'token_url' => 'https://github.com/login/oauth/access_token',
            'user_url' => 'https://api.github.com/user',
            'user_email_url' => 'https://api.github.com/user/emails',
            'scope' => 'user:email',
        ],
    ];

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'verify' => true,
        ]);
    }

    /**
     * Get OAuth configuration for a provider.
     */
    public function getConfig(string $provider): ?array
    {
        if (!isset($this->providers[$provider])) {
            return null;
        }

        $clientId = config("oauth.{$provider}.client_id");
        $clientSecret = config("oauth.{$provider}.client_secret");

        if (!$clientId || !$clientSecret) {
            return null;
        }

        return array_merge($this->providers[$provider], [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);
    }

    /**
     * Check if a provider is enabled.
     */
    public function isProviderEnabled(string $provider): bool
    {
        return $this->getConfig($provider) !== null;
    }

    /**
     * Get all enabled providers.
     */
    public function getEnabledProviders(): array
    {
        $enabled = [];
        foreach (array_keys($this->providers) as $provider) {
            if ($this->isProviderEnabled($provider)) {
                $enabled[] = $provider;
            }
        }
        return $enabled;
    }

    /**
     * Generate authorization URL for a provider.
     */
    public function getAuthorizationUrl(string $provider, string $redirectUri, string $state): ?string
    {
        $config = $this->getConfig($provider);
        if (!$config) {
            return null;
        }

        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $redirectUri,
            'scope' => $config['scope'],
            'response_type' => 'code',
            'state' => $state,
        ];

        if ($provider === 'google') {
            $params['access_type'] = 'offline';
            $params['prompt'] = 'consent';
        }

        return $config['authorize_url'] . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token.
     */
    public function getAccessToken(string $provider, string $code, string $redirectUri): ?array
    {
        $config = $this->getConfig($provider);
        if (!$config) {
            return null;
        }

        try {
            $params = [
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ];

            $headers = ['Accept' => 'application/json'];
            if ($provider === 'github') {
                $headers['Accept'] = 'application/json';
            }

            $response = $this->httpClient->post($config['token_url'], [
                'form_params' => $params,
                'headers' => $headers,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'access_token' => $data['access_token'] ?? null,
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_in' => $data['expires_in'] ?? null,
            ];
        } catch (GuzzleException $e) {
            SystemLog::error('OAuth token exchange failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get user info from OAuth provider.
     */
    public function getUserInfo(string $provider, string $accessToken): ?array
    {
        $config = $this->getConfig($provider);
        if (!$config) {
            return null;
        }

        try {
            $headers = [
                'Authorization' => "Bearer {$accessToken}",
                'Accept' => 'application/json',
            ];

            $response = $this->httpClient->get($config['user_url'], [
                'headers' => $headers,
            ]);

            $user = json_decode($response->getBody()->getContents(), true);

            // GitHub needs separate call for email
            if ($provider === 'github' && empty($user['email'])) {
                $emailResponse = $this->httpClient->get($config['user_email_url'], [
                    'headers' => $headers,
                ]);
                $emails = json_decode($emailResponse->getBody()->getContents(), true);
                foreach ($emails as $email) {
                    if ($email['primary'] ?? false) {
                        $user['email'] = $email['email'];
                        break;
                    }
                }
            }

            return $this->normalizeUserInfo($provider, $user);
        } catch (GuzzleException $e) {
            SystemLog::error('OAuth user info fetch failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Normalize user info from different providers.
     */
    protected function normalizeUserInfo(string $provider, array $user): array
    {
        $normalized = [
            'provider' => $provider,
            'provider_user_id' => (string) ($user['id'] ?? ''),
            'email' => $user['email'] ?? '',
            'name' => $user['name'] ?? ($user['login'] ?? ''),
            'avatar' => $user['picture'] ?? ($user['avatar_url'] ?? ''),
        ];

        return $normalized;
    }

    /**
     * Create or update OAuth provider record.
     */
    public function createOrUpdateProvider(array $userInfo, array $tokenInfo): OAuthProvider
    {
        $expiresAt = null;
        if (!empty($tokenInfo['expires_in'])) {
            $expiresAt = now()->addSeconds($tokenInfo['expires_in']);
        }

        return OAuthProvider::updateOrCreate(
            [
                'provider' => $userInfo['provider'],
                'provider_user_id' => $userInfo['provider_user_id'],
            ],
            [
                'email' => $userInfo['email'],
                'name' => $userInfo['name'],
                'avatar' => $userInfo['avatar'],
                'access_token' => $tokenInfo['access_token'] ?? null,
                'refresh_token' => $tokenInfo['refresh_token'] ?? null,
                'token_expires_at' => $expiresAt,
            ]
        );
    }
}
