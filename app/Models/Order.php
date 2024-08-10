<?php

namespace App\Models;

use App\Events\EventService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [

        'buyer_id',
        'seller_id',
        'service_id',
        'amount',
        'status',

    ];

    public function buyer(){
        return $this->belongsTo(User::class,'buyer_id');

    }
    public function seller(){
        return $this->belongsTo(User::class,'seller_id');

    }
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
    
    //    public function user(){
    //     return $this->belongsTo(User::class);

    // }

    public function service(){
        return $this->belongsTo(Services::class);

    }




}

