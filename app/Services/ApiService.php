<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;

class ApiService
{
    protected $client;
    protected $maxRetries = 5;
    protected $retryDelay = 2;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function makeRequest($url, $headers = [], $method = 'GET', $data = [])
    {
        $retries = 0;

        while ($retries < $this->maxRetries) {
            try {
                $response = $this->client->request($method, $url, [
                    'headers' => $headers,
                    'json' => $data,
                ]);

                return $response->getBody()->getContents();
            } catch (RequestException $e) {
                if ($e->hasResponse()) {
                    $statusCode = $e->getResponse()->getStatusCode();
                    if ($statusCode == 429) {
                        $waitTime = pow(2, $retries) * $this->retryDelay;
                        Log::warning("API rate limit exceeded. Retrying in {$waitTime} seconds...");
                        sleep($waitTime);
                        $retries++;
                        continue;
                    }
                }

                Log::error("Request failed: " . $e->getMessage());
                throw $e;
            }
        }

        throw new \Exception("Max retries reached. Could not complete the request.");
    }

    public function checkRateLimit($apiServiceName, $accountId)
    {
        $cacheKey = "rate_limit_{$apiServiceName}_{$accountId}";
        $requests = Cache::get($cacheKey, 0);

        if ($requests >= 100) {
            return false;
        }

        Cache::put($cacheKey, $requests + 1, now()->addHour());
        return true;
    }

    public function getFreshData($url, $headers = [], $method = 'GET', $data = [])
    {
        $cacheKey = "api_data_{$url}";

        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            return $cachedData;
        }

        $data = $this->makeRequest($url, $headers, $method, $data);
        Cache::put($cacheKey, $data, now()->addMinutes(30));

        return $data;
    }
}
