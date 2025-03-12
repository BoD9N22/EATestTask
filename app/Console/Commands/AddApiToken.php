<?php

namespace App\Console\Commands;

use App\Models\Token;
use App\Models\Account;
use Illuminate\Console\Command;

class AddApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-api-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountId = $this->argument('account_id');
        $token = $this->argument('token');
        $type = $this->argument('type');

        $account = Account::findOrFail($accountId);

        $apiToken = Token::create([
            'account_id' => $account->id,
            'token' => $token,
            'type' => $type,
        ]);

        $this->info("API токен для аккаунта '{$account->name}' был успешно добавлен!");
    }
}
