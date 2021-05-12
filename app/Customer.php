<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    
    protected $table = 'customers';

    protected $fillable = ['name','password','email','address','state_id','city_id','pincode','is_otp_verified','mobile'];

        protected $hidden = ['password'];


}
