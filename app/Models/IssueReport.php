<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IssueReport extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'user_id', 'admin_id', 'conversatione_id', 'message', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // public function conversations()
    // {
    //     return $this->morphOne(Conversatione::class, 'conversable');
    // }
    // public function conversation()
    // {
    //     return $this->belongsTo(Conversation::class, 'conversation_id');
    // }
    public function conversation()
    {
        return $this->hasOne(Conversatione::class, 'conversable_id')->where('conversable_type', self::class);
    }

    // public function reportable()
    // {
    //     return $this->morphTo();
    // }

    // public function messages()
    // {
    //     return $this->hasMany(Message::class);
    // }
}
