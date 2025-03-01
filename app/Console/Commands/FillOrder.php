<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FillOrder extends Command
{

    protected $signature = 'app:fill-order';

    protected $description = 'Command description';

    protected string $baseUrl = 'http://89.108.115.241:6969/api/orders';
    protected array $defaultParams = [
        'dateFrom' => '1999-10-10',
        'dateTo' => '2025-03-01',
        'key' => 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie',
        'limit' => 500,
    ];

    public function handle()
    {
        $this->info('Запрос к API начат. Обработка данных...');

        $params = $this->defaultParams;
        $params['page'] = 1;

        while (true) {
            $data = $this->fetchData($params);

            if (empty($data)) {
                $this->info("Данные на странице {$params['page']} отсутствуют.");
                break;
            }

            if (!$this->storeData($data, $params['page'])) {
                break;
            }

            if (!$this->hasNextPage($data, $params['page'])) {
                break;
            }

            $params['page']++;
        }

        $this->info('Все данные обработаны и записаны.');
    }

    protected function fetchData(array $params): array
    {
        $this->info("Запрос данных страницы: {$params['page']}");

        $response = Http::get($this->baseUrl, $params);

        if ($response->failed()) {
            $this->error("Ошибка запроса API на странице {$params['page']}: {$response->status()}");
            return [];
        }

        return $response->json() ?: [];
    }

    protected function storeData(array $data, int $page): bool
    {
        $insertData = $this->transformData($data['data'] ?? []);

        try {
            DB::table('orders')->insert($insertData);
            $this->info("Данные страницы {$page} успешно сохранены.");
            unset($insertData);
            gc_collect_cycles();
            return true;
        } catch (\Exception $e) {
            $this->error("Ошибка записи данных страницы {$page}: {$e->getMessage()}");
            return false;
        }
    }

    protected function transformData(array $data): array
    {
        return collect($data)->map(function ($item) {
            return [
                'g_number' => data_get($item, 'g_number'),
                'date' => data_get($item, 'date'),
                'last_change_date' => data_get($item, 'last_change_date'),
                'supplier_article' => data_get($item, 'supplier_article'),
                'tech_size' => data_get($item, 'tech_size'),
                'barcode' => data_get($item, 'barcode'),
                'total_price' => data_get($item, 'total_price'),
                'discount_percent' => data_get($item, 'discount_percent'),
                'warehouse_name' => data_get($item, 'warehouse_name'),
                'oblast' => data_get($item, 'oblast'),
                'income_id' => data_get($item, 'income_id'),
                'odid' => data_get($item, 'odid'),
                'nm_id' => data_get($item, 'nm_id'),
                'subject' => data_get($item, 'subject'),
                'category' => data_get($item, 'category'),
                'brand' => data_get($item, 'brand'),
                'is_cancel' => data_get($item, 'is_cancel'),
                'cancel_dt' => data_get($item, 'cancel_dt'),
            ];
        })->toArray();
    }

    protected function hasNextPage(array $data, int $currentPage): bool
    {
        $lastPage = data_get($data, 'meta.last_page', 1);
        return $currentPage < $lastPage;
    }
}
