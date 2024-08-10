<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
    protected $table='ratings';
    protected $fillable = [
        'service_id', 'client_id', 'quality_score', 'delivery_speed_score',
        'communication_score', 'deadline_adherence_score', 'overall_score', 'comment', 'rateable_id',
        'rateable_type'
    ];
    protected $dates = [
        'date',
    ];



    public function user()
    {
        return $this->belongsTo(User::class ,'client_id');
    }

    public function rateable()
    {
        return $this->morphTo();
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'rating_id');
    }

    // public function comments()
    // {
    //     return $this->morphMany(Comment::class, 'commentable');
    // }
    // public function comments()
    // {
    //     return $this->hasMany(Comment::class);
    // }

    // public function service()
    // {
    //     return $this->belongsTo(Services::class);
    // }

    // public function user()
    // {
    //     return $this->belongsTo(User::class , 'client_id');
    // }


    // public function buyerComments()
    // {
    //     return $this->hasMany(Comment::class)->where('type', 'client');
    // }

    // public function sellerComments()
    // {
    //     return $this->hasMany(Comment::class)->where('type', 'freelancer');
    // }

}
