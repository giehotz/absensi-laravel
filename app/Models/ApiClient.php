<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ApiClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'app_id',
        'api_key',
        'ip_whitelist',
        'rate_limit',
        'is_active',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
            'rate_limit' => 'integer',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ApiLog::class);
    }

    public static function generateKey(): string
    {
        return 'sk_live_'.Str::random(48);
    }

    public function isIpAllowed(?string $ip): bool
    {
        if (empty($this->ip_whitelist) || empty($ip)) {
            return true;
        }

        $allowedIps = array_filter(array_map('trim', explode(',', $this->ip_whitelist)));

        if (empty($allowedIps)) {
            return true;
        }

        return in_array($ip, $allowedIps, true);
    }
}
