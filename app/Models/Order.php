<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'buyer_id', 'total_price', 'status',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}