<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeJobCategory extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];



    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function job_category()
    {
        return $this->belongsTo(JobCategory::class);
    }
}
