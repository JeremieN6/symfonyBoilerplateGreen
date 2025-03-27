<?php

namespace App\Repository;

use App\Entity\Plan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Plan>
 */
class PlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plan::class);
    }

    public function findBusinessAnnualyPlan(): ?Plan
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name = :name')
            ->setParameter('name', 'Business Année')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBusinessMonthlyPlan(): ?Plan
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name = :name')
            ->setParameter('name', 'Business Mois')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBasicPlan(): ?Plan
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name = :name')
            ->setParameter('name', 'Basic')
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Plan[] Returns an array of Plan objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Plan
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
