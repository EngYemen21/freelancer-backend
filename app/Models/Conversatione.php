<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversatione extends Model
{
    use HasFactory;
    use SoftDeletes;
   protected $table='conversationes';
protected $fillable = ['user1_id', 'user2_id','service_id','project_id', 'conversable_id', 'conversable_type'];

public function messages()
{
    return $this->hasMany(Message::class);
}
public function user1()
{
    return $this->belongsTo(User::class,'user1_id');
}
public function user2()
{
    return $this->belongsTo(User::class,'user2_id');
}

public function conversable()
{
    return $this->morphTo();

}
// public function conversable()
// {
//     return $this->morphTo();
// }

// public function attechment()
// {

//     return $this->morphMany(Attechment::class, 'fileable');

// }
public function notifications()
{
    return $this->morphMany(Notificationes::class, 'notifiable');
}

public function project()
{
    return $this->belongsTo(Project::class, 'project_id');
}

public function service()
{
    return $this->belongsTo(Services::class, 'service_id');
}

}

