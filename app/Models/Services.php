<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Services extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'title',
        'description',
        'price',
        'delivery_time',
        'user_id',
        'status',
        'image'

    ];
    protected $dates = [
        'date',
    ];

    public function freelancer() {
        return $this->belongsTo(User::class, 'freelancer_id');
    }


    public function conversations()
    {
        return $this->morphOne(Conversatione::class, 'conversable');
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function skills()
    {
        return $this->morphToMany(Skill::class, 'skillable');
    }
    public function ratings() {
        return $this->morphMany(Rating::class, 'rateable');
    }
    public function payments()
    {
        return $this->morphMany(payments::class, 'morphable');
    }
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function calculateOverallScore()
    {
        return $this->ratings()->avg('overall_score');
    }
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }





}
