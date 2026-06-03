<?php

namespace App\Service;

use App\Entity\Model;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class OpenWebUiSyncService
{
    private LoggerInterface $logger;

    public function __construct(
        private OpenWebUiClientFactory $clientFactory,
        private EntityManagerInterface $entityManager,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @return array<string, array{models: int}|array{error: string}>
     */
    public function syncAll(?string $siteKey = null): array
    {
        $siteKeys = null !== $siteKey ? [$siteKey] : $this->clientFactory->getSiteKeys();
        $results = [];

        foreach ($siteKeys as $key) {
            try {
                $client = $this->clientFactory->createClient($key);
                $results[$key] = ['models' => $this->syncModels($key, $client)];
            } catch (\Throwable $e) {
                $results[$key] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

    private function syncModels(string $siteKey, OpenWebUiClient $client): int
    {
        $apiModels = $client->fetchModels();
        $groupMembers = $this->fetchGroupMembers($siteKey, $client);
        $modelRepository = $this->entityManager->getRepository(Model::class);
        $seenIds = [];
        $count = 0;

        foreach ($apiModels as $item) {
            $id = $item['id'];
            $seenIds[] = $id;
            [$userCount, $groupCount] = $this->countAccess($item, $groupMembers);
            $model = $modelRepository->findOneBy(['site' => $siteKey, 'externalId' => $id]);

            if (null === $model) {
                $model = new Model(
                    externalId: $id,
                    site: $siteKey,
                    name: $item['name'] ?? $id,
                    baseModelId: $item['base_model_id'] ?? null,
                    description: $item['meta']['description'] ?? null,
                    systemPrompt: $item['params']['system'] ?? null,
                    isActive: $item['is_active'] ?? true,
                    accessUserCount: $userCount,
                    accessGroupCount: $groupCount,
                );
                $this->entityManager->persist($model);
            } else {
                $model->setName($item['name'] ?? $id);
                $model->setBaseModelId($item['base_model_id'] ?? null);
                $model->setDescription($item['meta']['description'] ?? null);
                $model->setSystemPrompt($item['params']['system'] ?? null);
                $model->setIsActive($item['is_active'] ?? true);
                $model->setAccessUserCount($userCount);
                $model->setAccessGroupCount($groupCount);
            }

            if (isset($item['created_at'])) {
                $model->setCreatedAt(new \DateTimeImmutable('@'.$item['created_at']));
            }
            if (isset($item['updated_at'])) {
                $model->setUpdatedAt(new \DateTimeImmutable('@'.$item['updated_at']));
            }

            ++$count;
        }

        if ($count > 0) {
            $this->removeStaleEntities(Model::class, $seenIds, $siteKey);
        }
        $this->entityManager->flush();

        return $count;
    }

    /**
     * Build a map of group_id => list<user_id>. Returns null when the admin-only
     * groups endpoint is unreachable, signalling that group-based grants cannot
     * be resolved.
     *
     * @return array<string, list<string>>|null
     */
    private function fetchGroupMembers(string $siteKey, OpenWebUiClient $client): ?array
    {
        try {
            $map = [];
            foreach ($client->fetchGroups() as $group) {
                $map[$group['id']] = $group['user_ids'];
            }

            return $map;
        } catch (\Throwable $e) {
            $this->logger->warning('OpenWebUI groups fetch failed for site {site}: {error}', [
                'site' => $siteKey,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Returns [userCount, groupCount] for a model:
     *   - userCount: distinct users (owner + direct grants + members of any
     *     resolved group grants).
     *   - groupCount: number of distinct group grants that could not be
     *     resolved (because the admin groups endpoint is unavailable). Zero
     *     when all groups were expanded into userCount.
     *
     * @param array<string, mixed>             $model
     * @param array<string, list<string>>|null $groupMembers
     *
     * @return array{0: int, 1: int}
     */
    private function countAccess(array $model, ?array $groupMembers): array
    {
        $uniqueUsers = [];
        $unresolvedGroups = [];

        $ownerId = $model['user_id'] ?? null;
        if (is_string($ownerId) && '' !== $ownerId) {
            $uniqueUsers[$ownerId] = true;
        }

        foreach ($model['access_grants'] ?? [] as $grant) {
            $type = $grant['principal_type'] ?? null;
            $id = $grant['principal_id'] ?? null;
            if (!is_string($type) || !is_string($id)) {
                continue;
            }
            if ('user' === $type) {
                $uniqueUsers[$id] = true;
                continue;
            }
            if ('group' === $type) {
                if (null === $groupMembers) {
                    $unresolvedGroups[$id] = true;
                    continue;
                }
                foreach ($groupMembers[$id] ?? [] as $memberId) {
                    $uniqueUsers[$memberId] = true;
                }
            }
        }

        return [count($uniqueUsers), count($unresolvedGroups)];
    }

    /**
     * @param class-string $entityClass
     * @param list<string> $seenIds
     */
    private function removeStaleEntities(string $entityClass, array $seenIds, string $siteKey): void
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($entityClass, 'e')
            ->where('e.site = :site')
            ->setParameter('site', $siteKey);

        if ([] !== $seenIds) {
            $qb->andWhere('e.externalId NOT IN (:ids)')
                ->setParameter('ids', $seenIds);
        }

        foreach ($qb->getQuery()->getResult() as $entity) {
            $this->entityManager->remove($entity);
        }
    }
}
