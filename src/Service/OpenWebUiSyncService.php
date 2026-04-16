<?php

namespace App\Service;

use App\Entity\Model;
use Doctrine\ORM\EntityManagerInterface;

final class OpenWebUiSyncService
{
    public function __construct(
        private OpenWebUiClientFactory $clientFactory,
        private EntityManagerInterface $entityManager,
    ) {
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
            } catch (\InvalidArgumentException $e) {
                $results[$key] = ['error' => $e->getMessage()];
                continue;
            }

            $results[$key] = [
                'models' => $this->syncModels($key, $client),
            ];
        }

        return $results;
    }

    private function syncModels(string $siteKey, OpenWebUiClient $client): int
    {
        $apiModels = $client->fetchModels();
        $modelRepository = $this->entityManager->getRepository(Model::class);
        $seenIds = [];
        $count = 0;

        foreach ($apiModels as $item) {
            $id = $item['id'];
            $seenIds[] = $id;
            $model = $modelRepository->find($id);

            if (null === $model) {
                $model = new Model(
                    externalId: $id,
                    site: $siteKey,
                    name: $item['name'] ?? $id,
                    baseModelId: $item['base_model_id'] ?? null,
                    description: $item['meta']['description'] ?? null,
                    systemPrompt: $item['params']['system'] ?? null,
                    isActive: $item['is_active'] ?? true,
                );
                $this->entityManager->persist($model);
            } else {
                $model->setName($item['name'] ?? $id);
                $model->setBaseModelId($item['base_model_id'] ?? null);
                $model->setDescription($item['meta']['description'] ?? null);
                $model->setSystemPrompt($item['params']['system'] ?? null);
                $model->setIsActive($item['is_active'] ?? true);
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
