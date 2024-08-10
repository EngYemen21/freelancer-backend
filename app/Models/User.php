<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use React\Http\Io\Transaction;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstName',
        'lastName',
        'username',
        'role',
        'email',
        'is_banned',
        'email_verified_at',
        'email_verification_token',
        'email_verified',
        'date_of_birth',
        'phone_number',
        'city',
        'gender',
        'field',
        'summary',
        'Specialization',
        'image',
        'user_type',
         'social_id' ,
          'social_type',
        'password',

    ];
    // protected $attributes = [
    //     'created_at' => 'U',
    // ];
    protected $casts = [
        'email_verified_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated' => 'datetime:Y-m-d H:i:s',
    ];
//     public function users()
// {
//     return $this->belongsToMany(User::class, 'user_skills');
// }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    public function skills()
    {
        return $this->morphToMany(Skill::class, 'skillable');
    }
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

//     public function unreadNotifications()
// {
//     return $this->notifications()->unread();
// }
   public function buyer(){
        return $this->belongsTo(Order::class,'buyer_id');

    }
    public function seller(){
        return $this->belongsTo(Order::class,'seller_id');

    }
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }



    // public function ratings()
    // {
    //     return $this->hasMany(Rating::class,'client_id');
    // }

    public function balance()
    {
        return $this->hasOne(balances::class);
    }
    public function transactions()
    {
        return $this->hasMany(transactions::class);
    }
    public function ratings()
    {
        return $this->belongsTo(Rating::class);
    }

    public function payments()
    {
        return $this->hasMany(payments::class);
    }


    public function withdrawals()
    {
        return $this->hasMany(withdrawals::class);
    }


    public function emailverification(){
        $email = $this->email;
        $token = Str::random(40);
        $user = User::where('email',$email)->first();

        $user->update(['email_verification_token' => $token]);
        $link = env('FRONTEND_URL').'/'.'email-verification?token='.$token;
        \Mail::send([],[],function($message) use($email, $link){
            $message->to($email)
                    ->subject("Verify Your Email Address")
                    ->html("<p>Verify Your Email</p><br/><a href='".$link."'>Verify Email Address</a>");
        });
        return $link;
    }

public function notifications()
{
    return $this->hasMany(Notificationes::class);
}




public function comments() {
    return $this->hasMany(Comment::class);
}

    public function service(){
        return $this->hasMany(Services::class ,'user_id','id');
    }
    /////////////////////////Projects ////////////////
    public function projects()
    {
        return $this->hasMany(Project::class, 'user_id');
    }

    public function bids()
    {
        return $this->hasMany(Bid::class, 'freelancer_id');
    }



}
