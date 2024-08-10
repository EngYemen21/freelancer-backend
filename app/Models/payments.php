<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class payments extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
         'manual_payment',
        'amount',
        'payment_method',
        'payment_data',
        'payment_status',
        'morphable_id',
        'morphable_type',
    ];

    public function contract()
    {
        return $this->hasOne(Contract::class, 'payment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(transactions::class);
    }
    public function morphable()
    {
        return $this->morphTo();
    }
}
