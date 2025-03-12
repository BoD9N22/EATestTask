<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Token;
use App\Models\APIService;
use App\Models\Company;
use Illuminate\Console\Command;

class CreateAccountWithTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-account-with-tokens';

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
        $username = $this->argument('username');
        $password = $this->argument('password');

        $company = Company::find($companyId);
        $apiService = ApiService::find($apiServiceId);

        if (!$company || !$apiService) {
            $this->error('Компания или API-сервис не найдены.');
            return;
        }

        $account = Account::create([
            'company_id' => $companyId,
            'api_service_id' => $apiServiceId,
            'username' => $username,
            'password' => $password,
        ]);

        // Создаем токены для аккаунта
        foreach ($apiService->supported_token_types as $type) {
            Token::create([
                'account_id' => $account->id,
                'type' => $type,
                'token' => $this->generateToken($type),
            ]);
        }

        $this->info('Аккаунт и токены успешно созданы!');
    }
    private function generateToken($type)
    {
        return strtoupper(bin2hex(random_bytes(16))) . '_' . $type;
    }
}
