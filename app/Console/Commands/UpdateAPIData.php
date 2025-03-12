<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Models\Data;
use Illuminate\Support\Facades\Http;

class UpdateAPIData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-a-p-i-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $accounts = Account::with('tokens', 'api_service')->get();

        foreach ($accounts as $account) {
            $tokens = $account->tokens;
            foreach ($tokens as $token) {
                $response = Http::withHeaders([
                    'Authorization' => "{$token->type} {$token->token}",
                ])->get($account->api_service->api_url, [
                    'date' => now()->toDateString(),
                ]);

                if ($response->successful()) {
                    Data::updateOrCreate(
                        ['account_id' => $account->id, 'date' => now()->toDateString()],
                        ['data' => $response->json()]
                    );
                }
            }
        }

        $this->info('Данные успешно обновлены');
    }
}
