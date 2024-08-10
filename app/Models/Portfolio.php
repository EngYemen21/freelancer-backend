<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory;
    protected $table='portfolio';
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'thumbnail',
        'image',
        'url'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
