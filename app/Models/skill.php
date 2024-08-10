<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class skill extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
    ];

    public function skillable()
    {
        return $this->morphTo();
    }

//     public function services()
// {
//     return $this->belongsToMany(Services::class, 'services_skills', 'service_id', 'skill_id');
// }


public function projects()
{
    return $this->belongsToMany(Project::class);
}

}
