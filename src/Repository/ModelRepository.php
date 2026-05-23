<?php

namespace App\Repository;

use App\Entity\Model;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Model>
 */
class ModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Model::class);
    }

    /**
     * @return list<Model>
     */
    public function search(?string $site, ?string $query): array
    {
        $qb = $this->createQueryBuilder('m');

        if (null !== $site) {
            $qb->andWhere('m.site = :site')->setParameter('site', $site);
        }

        if (null !== $query && '' !== $query) {
            $qb->andWhere('LOWER(m.name) LIKE :query')
                ->setParameter('query', '%'.strtolower($query).'%');
        }

        /** @var list<Model> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }
}
