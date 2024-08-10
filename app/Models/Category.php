<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table='categories';
    protected $fillable = [

        'name',

    ];
    public function services(){
        return $this->hasMany(Services::class);

    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

   
}
