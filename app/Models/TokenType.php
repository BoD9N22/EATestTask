<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenType extends Model
{
    protected $fillable = ['name'];

    public function tokens()
    {
        return $this->hasMany(Token::class);
    }
}
