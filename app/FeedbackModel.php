<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FeedbackModel extends Model
{
    
    protected $table = 'customer_feedback';

    protected $fillable = ['customer_id','comments','rating'];

}
