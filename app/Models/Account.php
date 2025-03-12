<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['company_id', 'api_service_id', 'account_name'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function apiService()
    {
        return $this->belongsTo(ApiService::class);
    }

    public function tokens()
    {
        return $this->hasMany(Token::class);
    }
}
