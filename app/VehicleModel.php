<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    
    protected $table = 'user_vehicles';

    protected $fillable = ['customer_id','vehicle_no','owner_name','model_name','manufacture_year','insured','policy_no','policy_expiry_date','isActive'];

}
