<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    
    protected $table = 'customer_service_request';

    protected $fillable = ['customer_id', 'date','service_type_id','vehicle_no','problem_description','current_meter_reading','service_centre_id'];

    protected $hidden = ['password'];

}
