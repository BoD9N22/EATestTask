<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    protected $fillable = ['account_id', 'date', 'data'];
    public static function getFreshData($accountId)
    {
        return self::where('account_id', $accountId)
            ->where('date', '>=', now()->subDay())
            ->orderBy('date', 'desc')
            ->get();
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
