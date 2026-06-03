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
        $all = [];
        $page = 1;

        do {
            $response = $this->request('/api/v1/models/list', ['page' => $page]);
            $all = array_merge($all, $response['items'] ?? []);
            ++$page;
        } while (count($all) < ($response['total'] ?? 0));

        return $all;
    }

    /**
     * Each group is returned with its member user IDs merged in from /export,
     * because the list endpoint omits members.
     *
     * @return list<array{id: string, user_ids: list<string>}>
     */
    public function fetchGroups(): array
    {
        $groups = $this->request('/api/v1/groups/');

        $result = [];
        foreach ($groups as $group) {
            $id = $group['id'] ?? null;
            if (null === $id) {
                continue;
            }
            $export = $this->request('/api/v1/groups/id/'.rawurlencode($id).'/export');
            $userIds = [];
            foreach ($export['user_ids'] ?? [] as $userId) {
                if (is_string($userId)) {
                    $userIds[] = $userId;
                }
            }
            $result[] = [
                'id' => $id,
                'user_ids' => $userIds,
            ];
        }

        return $result;
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

    private function request(string $endpoint, array $query = []): array
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
            ],
        ];

        if ([] !== $query) {
            $options['query'] = $query;
        }

        $response = $this->httpClient->request('GET', $this->baseUrl.$endpoint, $options);

        return $response->toArray();
    }
}
