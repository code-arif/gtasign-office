<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialize extends Model
{
    
    protected $guarded = ['id'];
    public function employee_specializes()
    {
        return $this->hasMany(EmployeeSpecialize::class, 'specialize_id', 'id');
    }

    public function company_specializes()
    {
        return $this->hasMany(CompanySpecialize::class, 'specialize_id', 'id');
    }

}
