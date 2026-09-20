<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_client_id',
        'endpoint',
        'method',
        'ip_address',
        'user_agent',
        'status_code',
        'records_count',
        'response_time_ms',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'records_count' => 'integer',
            'response_time_ms' => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class, 'api_client_id');
    }
}
