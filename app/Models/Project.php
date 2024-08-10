<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title',
        'description','budget',
        'status', 'deadline'
        ,'dateTime',
        'duration_in_days',
        'category_id',

    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function payments()
    {
        return $this->morphOne(payments::class, 'morphable');
    }
    public function conversable()
    {
        return $this->morphTo();

    }
    // public function comments()
    // {
    //     return $this->morphMany(Comment::class, 'commentable');
    // }


    // public function ratings() {
    //     return $this->morphMany(Rating::class, 'rateable');
    // }

    // public function comments() {
    //     return $this->morphMany(Comment::class, 'commentable');
    // }



    // public function categories()
    // {
    //     return $this->morphToMany(Category::class, 'categorizable');
    // }

    public function conversations()
    {

        return $this->morphOne(Conversatione::class, 'conversable');

    }

    public function skills()
    {
        return $this->morphToMany(Skill::class, 'skillable');
    }
     // public function client() {
    //     return $this->belongsTo(User::class, 'client_id');
    // }

    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }
    // public function chatProject()
    // {
    //     return $this->hasOne(Project::class);
    // }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    // public function skills()
    // {
    //     return $this->belongsToMany(skillProject::class, 'project_skill', 'project_id', 'skill_projects_id');
    // }
    // public function skills()
    // {
    //     return $this->belongsToMany(Skill::class);
    // }

    // public function categories()
    // {
    //     return $this->belongsToMany(ProjectCategory::class, 'project_category_mapping', 'project_id', 'category_id');
    // }
    public function acceptedBid()
    {
        return $this->hasOne(AcceptedBid::class);
    }

    public function status()
    {
        return $this->hasOne(ProjectStatus::class);
    }


    // public function payments()
    // {
    //     return $this->hasMany(Payment::class);
    // }
}
