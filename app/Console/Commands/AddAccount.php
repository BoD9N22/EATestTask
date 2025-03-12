<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Company;
use Illuminate\Console\Command;

class AddAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-account';

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
        $companyId = $this->argument('company_id');
        $name = $this->argument('name');

        $company = Company::findOrFail($companyId);

        $account = Account::create([
            'company_id' => $company->id,
            'name' => $name,
        ]);

        $this->info("Аккаунт '{$account->name}' был успешно добавлен для компании '{$company->name}'!");
    }
}
