<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings'));
        static::deleted(fn () => Cache::forget('settings'));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever('settings', fn () => static::pluck('value', 'key'))
            ->get($key, $default);
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings');
    }

    /**
     * Create the key only if it does not exist yet, so re-running the seeder
     * never overwrites a value the client has filled in.
     */
    public static function setIfMissing(string $key, mixed $value): void
    {
        if (! static::where('key', $key)->exists()) {
            static::create(['key' => $key, 'value' => $value]);
            Cache::forget('settings');
        }
    }

    /** True when the key holds a non-empty value. */
    public static function filled(string $key): bool
    {
        return trim((string) static::get($key)) !== '';
    }
}
