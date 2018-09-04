<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Vacation extends Model
{
    protected $table = 'vacations';
    public $timestamps = true;

    use SoftDeletes,LogsActivity;

    protected $dates = ['created_at','updated_at','deleted_at'];
    protected $fillable = [
        'type',
        'num_of_days',
        'added_to',
        'vacation_type_id',
        'staff_id',
        'notes',
        'decision',
        'vacation_start',
        'vacation_end',
        ];

    public function staff()
    {
        return $this->belongsTo('App\Models\Staff');
    }
    public function addedTo()
    {
        return $this->belongsTo('App\Models\Staff','added_to');
    }
}
