<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceCentreModel extends Model
{
    
    protected $table = 'service_centres';

    protected $fillable = ['name', 'address','contact_no','state_id','city_id','admin_id','about_centre'];


}
