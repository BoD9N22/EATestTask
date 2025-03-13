<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Token;
use App\Models\APIService;
use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AddAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-account {companyId} {apiServiceId} {tokenTypeId}';

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
        $companyId = $this->argument('companyId');
        $apiServiceId = $this->argument('apiServiceId');
        $tokenTypeId = $this->argument('tokenTypeId');

        $company = Company::find($companyId);
        $apiService = ApiService::find($apiServiceId);

        if (!$company || !$apiService) {
            $this->error('Компания или API-сервис не найдены.');
            return;
        }

        $account = Account::create([
            'company_id' => $companyId,
            'api_service_id' => $apiServiceId,
        ]);

        $token = Token::create([
            'token_type_id' => $tokenTypeId,
            'value' => Str::random(),
        ]);

        $account->token_id = $token->id;
        $account->save();

        $this->info('Аккаунт и токены успешно созданы!');
    }
    private function generateToken($type)
    {
        return strtoupper(bin2hex(random_bytes(16))) . '_' . $type;
    }
}
