<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'user_id',
        'image_url',
        'name',
        'display_name',
        'location',
        'size',
        'website_url',
        'bio',
        'past_project',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company_specializes()
    {
        return $this->hasMany(CompanySpecialize::class, 'company_id', 'id');
    }
    public function company_projects()
    {
        return $this->hasMany(CompanyProject::class, 'company_id', 'id');
    }
    public function getImageUrlAttribute($value)
    {
        return $value ? url($value) : null;
    }

    public function CompanyJobs()
    {
        return $this->hasMany(CompanyJob::class, 'company_id', 'id');
    }

}
