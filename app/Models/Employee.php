<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = ['id'];


    protected $hidden = ['created_at', 'updated_at'];

    public function getImageUrlAttribute($value)
    {
        return $value ? url($value) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }



    public function experiences()
    {
        return $this->hasMany(EmployeeExperience::class);
    }

    public function certifications()
    {
        return $this->hasMany(EmployeeCertification::class);
    }


    public function qualifications()
    {
        return $this->hasMany(EmployeeQualification::class, 'employee_id', 'id');
    }


    public function specializations()
    {
        return $this->hasMany(EmployeeSpecialize::class, 'employee_id', 'id');
    }

    public function employee_job_categories()
    {
        return $this->hasMany(EmployeeJobCategory::class);
    }


}
