<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $fillable = ['conversatione_id', 'user_id', 'content'];

    public function conversation()
    {
        return $this->belongsTo(Conversatione::class);
    }
    public function attechment()
{

    return $this->morphMany(Attechment::class, 'fileable');

}

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function notifications()
    {
        return $this->morphMany(Notificationes::class, 'notifiable');
    }
}
