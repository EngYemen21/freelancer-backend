<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcceptedBid extends Model
{
    use HasFactory;
    protected $table='accepted_bids';

    protected $fillable = [
        'project_id', 'bid_id', 'freelancer_id', 'accepted_at'
    ];

    public function ratings() {
        return $this->morphMany(Rating::class, 'rateable');
    }
        public function conversations()
    {
        return $this->morphOne(Conversatione::class, 'conversable');
    }
    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'accepted_bids_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function bid()
    {
        return $this->belongsTo(Bid::class);
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

}
