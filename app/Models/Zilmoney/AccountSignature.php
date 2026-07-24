<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Model;

class AccountSignature extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'path',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->path) return null;
        if (filter_var($this->path, FILTER_VALIDATE_URL)) {
            return $this->path;
        }
        return asset('storage/' . $this->path);
    }
}
