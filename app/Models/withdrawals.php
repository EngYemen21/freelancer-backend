<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class withdrawals extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'amount',
        'withdrawal_method',
        'withdrawal_date',
        'withdrawal_status',
        'commission',

    ];

    protected $casts = [
        'amount' => 'float',
        'withdrawal_date' => 'date',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(transactions::class);
    }
}
