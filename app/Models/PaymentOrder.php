<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentOrder extends Model
{
    protected $table = 'payment_orders';

    protected $fillable = [
        'order_code',
        'midtrans_order_id',
        'user_id',
        'outlet_id',
        'recipient_name',
        'recipient_phone',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'delivery_distance_km',
        'items_count',
        'subtotal_amount',
        'discount_percent',
        'shipping_fee',
        'total_amount',
        'currency',
        'payment_gateway',
        'snap_token',
        'payment_status',
        'midtrans_transaction_status',
        'midtrans_payment_type',
        'midtrans_transaction_id',
        'midtrans_fraud_status',
        'paid_at',
        'stock_processed_at',
        'expired_at',
        'meta',
        'midtrans_response',
    ];

    protected $casts = [
        'meta' => 'array',
        'midtrans_response' => 'array',
        'paid_at' => 'datetime',
        'stock_processed_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PaymentOrderItem::class, 'payment_order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }
}
