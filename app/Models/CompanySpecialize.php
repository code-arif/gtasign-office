<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySpecialize extends Model
{
    protected $fillable = [
        'company_id',
        'specialize_id',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function specialize()
    {
        return $this->belongsTo(Specialize::class);
    }
}
