<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OpenWebUiClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
        private string $apiKey,
    ) {
    }

    public function fetchModels(): array
    {
        return $this->request('/api/v1/models/list')['items'] ?? [];
    }

    public function isHealthy(): bool
    {
        try {
            $response = $this->request('/health');

            return true === ($response['status'] ?? false);
        } catch (\Throwable) {
            return false;
        }
    }

    private function request(string $endpoint): array
    {
        $response = $this->httpClient->request('GET', $this->baseUrl.$endpoint, [
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
            ],
        ]);

        return $response->toArray();
    }
}
