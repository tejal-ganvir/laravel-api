<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model
{
    
    protected $table = 'admin_master';

    protected $fillable = ['name', 'mobile','password','isActive'];

    protected $hidden = ['password'];


}
