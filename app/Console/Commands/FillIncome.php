<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FillIncome extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-income';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    protected string $baseUrl = 'http://89.108.115.241:6969/api/incomes';
    protected array $params = [
        'dateFrom' => '1999-02-28',
        'dateTo' => '2025-03-01',
        'page' => 1,
        'key' => 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie',
        'limit' => 500,
    ];

    public function handle()
    {
        $this->info('Запрос к API начат. Данные будут записываться по порядку...');

        while (true) {
            $this->info("Запрос данных страницы: {$this->params['page']}");

            $response = $this->fetchPage();

            if (!$response) {
                break;
            }

            $data = $response['data'] ?? [];
            if (empty($data)) {
                $this->info("Нет данных для страницы: {$this->params['page']}");
                break;
            }

            $this->saveData($data);

            if ($this->params['page'] >= ($response['meta']['last_page'] ?? 1)) {
                break;
            }

            $this->params['page']++;
        }

        $this->info('Все доступные данные успешно обработаны и записаны.');
    }

    protected function fetchPage(): ?array
    {
        $response = Http::get($this->baseUrl, $this->params);

        if ($response->failed()) {
            $this->error("Ошибка запроса API на странице {$this->params['page']}: {$response->status()}");
            return null;
        }

        return $response->json();
    }

    protected function saveData(array $data): void
    {
        $insertData = array_map(fn($item) => [
            'income_id' => data_get($item, 'income_id'),
            'number' => data_get($item, 'number'),
            'date' => data_get($item, 'date'),
            'last_change_date' => data_get($item, 'last_change_date'),
            'supplier_article' => data_get($item, 'supplier_article'),
            'tech_size' => data_get($item, 'tech_size'),
            'barcode' => data_get($item, 'barcode'),
            'quantity' => data_get($item, 'quantity'),
            'total_price' => data_get($item, 'total_price'),
            'date_close' => data_get($item, 'date_close'),
            'warehouse_name' => data_get($item, 'warehouse_name'),
            'nm_id' => data_get($item, 'nm_id'),
        ], $data);

        try {
            DB::table('incomes')->insert($insertData);
            $this->info("Данные страницы {$this->params['page']} успешно записаны в базу.");
        } catch (\Exception $e) {
            $this->error("Ошибка записи данных страницы {$this->params['page']}: {$e->getMessage()}");
        }
    }
}
