<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disputes extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'freelancer_id',
        'issue',
        'status',
    ];
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }
}
