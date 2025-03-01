<?php

namespace App\Console\Commands;

use App\Models\Stock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FillStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fillStock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    protected string $baseUrl = 'http://89.108.115.241:6969/api/stocks';
    protected array $params = [
        'dateFrom' => '2025-03-01',
        'dateTo' => '2025-03-01',
        'page' => 1,
        'key' => 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie',
        'limit' => 500,
    ];

    public function handle()
    {
        $this->info('Начинается запрос к API и запись данных...');

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

            $this->upsertData($data);

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

    protected function upsertData(array $data): void
    {
        $insertData = array_map(fn($item) => [
            'date' => data_get($item, 'date'),
            'last_change_date' => data_get($item, 'last_change_date'),
            'supplier_article' => data_get($item, 'supplier_article'),
            'tech_size' => data_get($item, 'tech_size'),
            'barcode' => data_get($item, 'barcode'),
            'quantity' => data_get($item, 'quantity'),
            'is_supply' => data_get($item, 'is_supply'),
            'is_realization' => data_get($item, 'is_realization'),
            'quantity_full' => data_get($item, 'quantity_full'),
            'warehouse_name' => data_get($item, 'warehouse_name'),
            'in_way_to_client' => data_get($item, 'in_way_to_client'),
            'in_way_from_client' => data_get($item, 'in_way_from_client'),
            'nm_id' => data_get($item, 'nm_id'),
            'subject' => data_get($item, 'subject'),
            'category' => data_get($item, 'category'),
            'brand' => data_get($item, 'brand'),
            'sc_code' => data_get($item, 'sc_code'),
            'price' => data_get($item, 'price'),
            'discount' => data_get($item, 'discount'),
        ], $data);

        try {
            DB::table('stocks')->upsert($insertData, ['barcode'], [
                'quantity', 'is_supply', 'is_realization', 'quantity_full', 'updated_at',
            ]);
            $this->info("Данные страницы {$this->params['page']} успешно записаны в базу.");
        } catch (\Exception $e) {
            $this->error("Ошибка записи данных страницы {$this->params['page']}: {$e->getMessage()}");
        }
    }
}
