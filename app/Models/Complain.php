<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Complain extends Model
{
    protected $table = 'complains';
    public $timestamps = true;

    use SoftDeletes,LogsActivity;

    protected $dates = ['created_at','updated_at','deleted_at'];
    protected $fillable = [
        'project_id',
        'call_details',
        'staff_id',
        'complain_of_staff_id',
        'complain_client_id',
        'order_date',
        'call_id'
    ];

    public function staff()
    {
        return $this->belongsTo('App\Models\Staff');
    }
    public function complainOfStaff()
    {
        return $this->belongsTo('App\Models\Staff','complain_of_staff_id');
    }
    public function project(){
        return $this->belongsTo('App\Models\Project','project_id');
    }
    public function client()
    {
        return  $this->belongsTo('App\Models\Client','complain_client_id');
    }
}
