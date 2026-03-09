<?php

namespace App\Service;

use App\Entity\AccessGrant;
use App\Entity\Group;
use App\Entity\Model;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class OpenWebUiSyncService
{
    public function __construct(
        private OpenWebUiClient $client,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function syncModels(): int
    {
        $apiModels = $this->client->fetchModels();
        $modelRepository = $this->entityManager->getRepository(Model::class);
        $userRepository = $this->entityManager->getRepository(User::class);
        $seenIds = [];
        $count = 0;

        foreach ($apiModels as $item) {
            $id = $item['id'];
            $seenIds[] = $id;
            $model = $modelRepository->find($id);
            $owner = isset($item['user_id']) ? $userRepository->find($item['user_id']) : null;

            if (null === $model) {
                $model = new Model(
                    externalId: $id,
                    name: $item['name'] ?? $id,
                    baseModelId: $item['base_model_id'] ?? null,
                    description: $item['meta']['description'] ?? null,
                    systemPrompt: $item['params']['system'] ?? null,
                    isActive: $item['is_active'] ?? true,
                    owner: $owner,
                );
                $this->entityManager->persist($model);
            } else {
                $model->setName($item['name'] ?? $id);
                $model->setBaseModelId($item['base_model_id'] ?? null);
                $model->setDescription($item['meta']['description'] ?? null);
                $model->setSystemPrompt($item['params']['system'] ?? null);
                $model->setIsActive($item['is_active'] ?? true);
                $model->setOwner($owner);
            }

            if (isset($item['created_at'])) {
                $model->setCreatedAt(new \DateTimeImmutable('@'.$item['created_at']));
            }
            if (isset($item['updated_at'])) {
                $model->setUpdatedAt(new \DateTimeImmutable('@'.$item['updated_at']));
            }

            $this->syncAccessGrants($model, $item['access_grants'] ?? []);

            ++$count;
        }

        $this->removeStaleEntities(Model::class, $seenIds);
        $this->entityManager->flush();

        return $count;
    }

    public function syncUsers(): int
    {
        $apiUsers = $this->client->fetchUsers();
        $repository = $this->entityManager->getRepository(User::class);
        $groupRepository = $this->entityManager->getRepository(Group::class);
        $seenIds = [];
        $count = 0;

        foreach ($apiUsers['users'] as $item) {
            $id = $item['id'];
            $seenIds[] = $id;
            $user = $repository->find($id);

            if (null === $user) {
                $user = new User(
                    externalId: $id,
                    name: $item['name'] ?? '',
                    email: $item['email'] ?? '',
                    role: $item['role'] ?? 'user',
                );
                $this->entityManager->persist($user);
            } else {
                $user->setName($item['name'] ?? '');
                $user->setEmail($item['email'] ?? '');
                $user->setRole($item['role'] ?? 'user');
                $user->setUpdatedAt(new \DateTimeImmutable());
            }

            $user->setUsername($item['username'] ?? null);

            if (isset($item['last_active_at'])) {
                $user->setLastActiveAt(new \DateTimeImmutable('@'.$item['last_active_at']));
            }

            $user->clearGroups();
            foreach ($item['group_ids'] ?? [] as $groupId) {
                $group = $groupRepository->find($groupId);
                if (null !== $group) {
                    $user->addGroup($group);
                }
            }

            ++$count;
        }

        $this->removeStaleEntities(User::class, $seenIds);
        $this->entityManager->flush();

        return $count;
    }

    public function syncGroups(): int
    {
        $apiGroups = $this->client->fetchGroups();
        $repository = $this->entityManager->getRepository(Group::class);
        $seenIds = [];
        $count = 0;

        foreach ($apiGroups as $item) {
            $id = $item['id'];
            $seenIds[] = $id;
            $group = $repository->find($id);
            $memberCount = $item['member_count'] ?? 0;

            if (null === $group) {
                $group = new Group(
                    externalId: $id,
                    name: $item['name'] ?? '',
                    description: $item['description'] ?? null,
                    memberCount: $memberCount,
                );
                $this->entityManager->persist($group);
            } else {
                $group->setName($item['name'] ?? '');
                $group->setDescription($item['description'] ?? null);
                $group->setMemberCount($memberCount);
                $group->setUpdatedAt(new \DateTimeImmutable());
            }

            ++$count;
        }

        $this->removeStaleEntities(Group::class, $seenIds);
        $this->entityManager->flush();

        return $count;
    }

    /**
     * @param array<array<string, mixed>> $grants
     */
    private function syncAccessGrants(Model $model, array $grants): void
    {
        $model->clearAccessGrants();
        $this->entityManager->flush();

        foreach ($grants as $grantData) {
            $grant = new AccessGrant(
                externalId: $grantData['id'],
                model: $model,
                resourceType: $grantData['resource_type'] ?? '',
                resourceId: $grantData['resource_id'] ?? '',
                principalType: $grantData['principal_type'] ?? '',
                principalId: $grantData['principal_id'] ?? '',
                permission: $grantData['permission'] ?? '',
            );

            if (isset($grantData['created_at'])) {
                $grant->setCreatedAt(new \DateTimeImmutable('@'.$grantData['created_at']));
            }

            $model->addAccessGrant($grant);
            $this->entityManager->persist($grant);
        }
    }

    /**
     * @param class-string $entityClass
     * @param list<string> $seenIds
     */
    private function removeStaleEntities(string $entityClass, array $seenIds): void
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($entityClass, 'e');

        if ([] !== $seenIds) {
            $qb->where('e.externalId NOT IN (:ids)')
                ->setParameter('ids', $seenIds);
        }

        foreach ($qb->getQuery()->getResult() as $entity) {
            $this->entityManager->remove($entity);
        }
    }

    public function syncAll(): array
    {
        return [
            'groups' => $this->syncGroups(),
            'users' => $this->syncUsers(),
            'models' => $this->syncModels(),
        ];
    }
}
