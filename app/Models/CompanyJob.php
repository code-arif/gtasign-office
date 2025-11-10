<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyJob extends Model
{
    protected $guarded = ['id'];


    protected $hidden = ['created_at', 'updated_at'];

    public function getImageUrlAttribute($value)
    {
        return url($value);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    public function job_category()
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id', 'id');
    }

    // job applicants
    public function jobApplicants()
    {
        return $this->hasMany(JobApplicant::class, 'job_id', 'id');
    }



}
