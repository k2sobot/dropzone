<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'level',
        'message',
        'context',
        'extra',
    ];

    protected $casts = [
        'context' => 'array',
        'extra' => 'array',
    ];

    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_NOTICE = 'notice';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';

    /**
     * Log a message.
     */
    public static function log( string $level, string $message, array $context = [], array $extra = [] ): self
    {
        return self::create( [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'extra' => $extra,
        ] );
    }

    /**
     * Log info message.
     */
    public static function info( string $message, array $context = [], array $extra = [] ): self
    {
        return self::log( self::LEVEL_INFO, $message, $context, $extra );
    }

    /**
     * Log warning message.
     */
    public static function warning( string $message, array $context = [], array $extra = [] ): self
    {
        return self::log( self::LEVEL_WARNING, $message, $context, $extra );
    }

    /**
     * Log error message.
     */
    public static function error( string $message, array $context = [], array $extra = [] ): self
    {
        return self::log( self::LEVEL_ERROR, $message, $context, $extra );
    }

    /**
     * Get level color for display.
     */
    public function getLevelColorAttribute(): string
    {
        return match( $this->level ) {
            self::LEVEL_DEBUG => 'secondary',
            self::LEVEL_INFO => 'info',
            self::LEVEL_NOTICE => 'primary',
            self::LEVEL_WARNING => 'warning',
            self::LEVEL_ERROR => 'danger',
            self::LEVEL_CRITICAL => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Scope for specific level.
     */
    public function scopeLevel( $query, string $level )
    {
        return $query->where( 'level', $level );
    }

    /**
     * Scope for errors and above.
     */
    public function scopeErrors( $query )
    {
        return $query->whereIn( 'level', [ self::LEVEL_ERROR, self::LEVEL_CRITICAL ] );
    }
}
