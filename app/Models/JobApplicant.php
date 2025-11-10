<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplicant extends Model
{
    
    protected $fillable = [
        'company_id',
        'job_id',
        'employee_id',
        'full_name',
        'email',
        'cell_number',
        'address',
        'resume',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id' ,'id');
    }

    public function job()
    {
        return $this->belongsTo(CompanyJob::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getResumeUrlAttribute($value)
    {
        return url($value);
    }
    
}
