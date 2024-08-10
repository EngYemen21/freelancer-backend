<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectStatus extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id', 'status', 'status_changed_at'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function conversable()
{
    return $this->morphTo();

}
}
