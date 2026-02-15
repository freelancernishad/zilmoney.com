<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeviceLog extends Model
{
    use HasFactory;

    protected $table = 'user_activity_logs'; // Map to existing table

    protected $fillable = [
        'user_id',
        'activity',
        'category',
        'ip',
        'device',
        'platform',
        'browser',
        'details',
        'is_success',
    ];
    
    protected $casts = [
        'is_success' => 'boolean',
        'details' => 'array',
    ];

    protected $appends = [
        'device_name',
        'ip_address',
        'last_login_at',
    ];

    public function getDeviceNameAttribute()
    {
        return $this->device ?? 'Unknown';
    }

    public function getIpAddressAttribute()
    {
        return $this->ip;
    }

    public function getLastLoginAtAttribute()
    {
        return $this->created_at;
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
