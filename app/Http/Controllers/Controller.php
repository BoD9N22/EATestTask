<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Data;
use Illuminate\Http\Request;

class DataController extends Controller
{
    public function getFreshData(Request $request, $accountId)
    {
        $account = Account::findOrFail($accountId);

        $freshData = Data::where('account_id', $account->id)
            ->where('date', '>=', now()->subDay())
            ->get();

        return response()->json($freshData);
    }
}
