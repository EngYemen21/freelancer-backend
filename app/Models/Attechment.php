<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attechment extends Model
{
    use HasFactory;
    protected $table='attachment';
    protected $fillable=[
        // 'chat_id',
        'filename',
        'file_type',
        'file_path',
        'file_size',
    ];
    public function fileable()
{
    return $this->morphTo();
}
}
