<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class transactions extends Model
{
    use HasFactory;
   

    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'status',
        'details',
    ];
    protected $casts = [
        'amount' => 'float',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
