<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AdminSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting?->value ?? $default;
        });
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("setting.{$key}");
    }

    /**
     * Clear the settings cache.
     */
    public static function clearCache(): void
    {
        $keys = static::pluck('key');

        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
    }

    /**
     * Get the background image URL.
     */
    public static function getBackgroundImage(): ?string
    {
        $path = static::get('background_image');

        if ($path) {
            return asset("storage/{$path}");
        }

        return null;
    }

    /**
     * Get the max file size in bytes.
     */
    public static function getMaxFileSize(): int
    {
        return (int) static::get('max_file_size', 104857600); // 100MB default
    }

    /**
     * Get the site name.
     */
    public static function getSiteName(): string
    {
        return static::get('site_name', 'Dropzone');
    }
}
