<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class balances extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'total_balance',
        'withdrawable_balance',
        'pending_balance',
        'total_earnings',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
