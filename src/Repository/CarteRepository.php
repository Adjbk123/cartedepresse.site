<?php

namespace App\Repository;

use App\Entity\Carte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Carte>
 */
class CarteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Carte::class);
    }

    public function countExpiredCards(): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->innerJoin('c.demande', 'd') // Jointure avec la table 'demande'
            ->where('c.dateExpiration < :today')
            ->andWhere('d.isPrinted = 1')
            ->setParameter('today', new \DateTime());

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    public function countForYear(int $year): int
    {
        $qb = $this->createQueryBuilder('c');

        $start = (new \DateTime("$year-01-01"))->setTime(0, 0, 0);
        $end = (new \DateTime("$year-12-31"))->setTime(23, 59, 59);

        return (int) $qb
            ->select('COUNT(c.id)')
            ->where('c.dateDelivrance BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * @return Carte[] Returns an array of Carte objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Carte
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
