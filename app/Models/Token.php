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

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (self::where('account_id', $model->account_id)->where('value', $model->value)->exists()) {
                throw new \Exception('Token already exists for this account');
            }
        });
    }
}
