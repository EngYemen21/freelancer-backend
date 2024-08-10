<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
    protected $table = 'contracts';

    protected $fillable = [

        'price',
        'start_date',
        'end_date',
        'status',
        'client_id',
        'service_id',
        'payment_id',
        'freelancer_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'contracts_id');
    }
    public function ratings() {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function conversations()
    {
        return $this->morphMany(Conversatione::class, 'conversable');
    }

    public function payments()
    {
        return $this->hasOne(payments::class ,'payment_id');
    }


    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class , 'client_id');
    }
  

    public function service()
    {
        return $this->belongsTo(Services::class);
    }
}
