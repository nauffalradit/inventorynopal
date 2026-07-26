<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['order_id', 'provider', 'invoice_number', 'request_id', 'amount', 'status', 'checkout_url', 'gateway_response', 'paid_at'];
    protected function casts(): array { return ['gateway_response' => 'array', 'paid_at' => 'datetime']; }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
