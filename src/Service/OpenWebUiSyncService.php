<?php

namespace App\Service;

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
        $repository = $this->entityManager->getRepository(Model::class);
        $count = 0;

        foreach ($apiModels as $item) {
            $id = $item['id'];
            $model = $repository->find($id);

            if (null === $model) {
                $model = new Model(
                    externalId: $id,
                    name: $item['name'] ?? $id,
                    ownedBy: $item['owned_by'] ?? null,
                );
                $this->entityManager->persist($model);
            } else {
                $model->setName($item['name'] ?? $id);
                $model->setOwnedBy($item['owned_by'] ?? null);
                $model->setUpdatedAt(new \DateTimeImmutable());
            }

            ++$count;
        }

        $this->entityManager->flush();

        return $count;
    }

    public function syncUsers(): int
    {
        $apiUsers = $this->client->fetchUsers();
        $repository = $this->entityManager->getRepository(User::class);
        $count = 0;

        foreach ($apiUsers['users'] as $item) {
            $id = $item['id'];
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

            if (isset($item['last_active_at'])) {
                $user->setLastActiveAt(new \DateTimeImmutable('@'.$item['last_active_at']));
            }

            ++$count;
        }

        $this->entityManager->flush();

        return $count;
    }

    public function syncGroups(): int
    {
        $apiGroups = $this->client->fetchGroups();
        $repository = $this->entityManager->getRepository(Group::class);
        $count = 0;

        foreach ($apiGroups as $item) {
            $id = $item['id'];
            $group = $repository->find($id);
            $userCount = \count($item['user_ids'] ?? []);

            if (null === $group) {
                $group = new Group(
                    externalId: $id,
                    name: $item['name'] ?? '',
                    description: $item['description'] ?? null,
                    userCount: $userCount,
                );
                $this->entityManager->persist($group);
            } else {
                $group->setName($item['name'] ?? '');
                $group->setDescription($item['description'] ?? null);
                $group->setUserCount($userCount);
                $group->setUpdatedAt(new \DateTimeImmutable());
            }

            ++$count;
        }

        $this->entityManager->flush();

        return $count;
    }

    public function syncAll(): array
    {
        return [
            'models' => $this->syncModels(),
            'users' => $this->syncUsers(),
            'groups' => $this->syncGroups(),
        ];
    }
}
