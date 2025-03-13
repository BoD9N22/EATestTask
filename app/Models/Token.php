<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $fillable = ['account_id', 'token_type_id', 'value'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function tokenType()
    {
        return $this->belongsTo(TokenType::class);
    }

}
