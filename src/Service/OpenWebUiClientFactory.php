<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OpenWebUiClientFactory
{
    /**
     * @param array<string, array{base_url: string, api_key: string}> $sites
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        private array $sites,
    ) {
    }

    public function createClient(string $siteKey): OpenWebUiClient
    {
        if (!isset($this->sites[$siteKey])) {
            throw new \InvalidArgumentException(sprintf('Unknown site "%s". Configured sites: %s', $siteKey, implode(', ', array_keys($this->sites))));
        }

        $config = $this->sites[$siteKey];

        if (!isset($config['base_url']) || empty($config['base_url']) || !isset($config['api_key']) || empty($config['api_key'])) {
            throw new \InvalidArgumentException(sprintf('Site "%s" is missing required configuration. Set OPENWEBUI_%s_BASE_URL and OPENWEBUI_%s_API_KEY environment variables.', $siteKey, strtoupper($siteKey), strtoupper($siteKey)));
        }

        return new OpenWebUiClient($this->httpClient, $config['base_url'], $config['api_key']);
    }

    /**
     * @return list<string>
     */
    public function getSiteKeys(): array
    {
        return array_keys($this->sites);
    }
}
