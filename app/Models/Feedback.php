<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
    protected $fillable = [
        'accepted_bids_id',
        'contracts_id',
        'rating_id',
        'comment',
    ];
    public function acceptedBid()
    {
        return $this->belongsTo(AcceptedBid::class, 'accepted_bids_id');
    }
    
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contracts_id');
    }

    public function rating()
    {
        return $this->belongsTo(Rating::class, 'rating_id');
    }
}
