<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Order extends Model
{
    use HasFactory;

    protected $table = 'shop_orders';

    protected $fillable = [
        'order_number',
        'payment_token',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'billing_address',
        'total_amount',
        'payment_status',
        'payment_method',
        'payment_link',
        'order_status',
        'tracking_number',
        'custom_check_details',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->payment_token)) {
                $order->payment_token = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $casts = [
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'custom_check_details' => 'array',
        'total_amount' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
