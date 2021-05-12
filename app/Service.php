<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    
    protected $table = 'services';

	protected $fillable = ['name', 'service_description','service_centre_id','isActive'];

}
