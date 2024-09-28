<?php

namespace App\Repository;

use App\Entity\Demande;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Demande>
 */
class DemandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demande::class);
    }

    public function findDerniereDemandePourAnnee(int $annee): ?Demande
    {

        return $this->createQueryBuilder('d')
            ->andWhere('SUBSTRING(d.numDemande, 1, 4) = :annee')
            ->setParameter('annee', $annee)
            ->orderBy('d.numDemande', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countExpiredCards(): int
    {
        $qb = $this->createQueryBuilder('d')
            ->select('count(d.id)')
            ->where('d.dateExpiration < :today')
            ->setParameter('today', new \DateTime())
            ->andWhere('d.isPrinted = 1');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    public function getAvailableYears(): array
    {
        $qb = $this->createQueryBuilder('d')
            ->select('d.dateSoumission')
            ->getQuery();

        $results = $qb->getResult();
        return array_map(function($result) {
            return $result['dateSoumission'];
        }, $results);
    }


    public function getStatisticsByYear(int $year): array
    {

        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('YEAR', 'DoctrineExtensions\Query\Mysql\Year');
        $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
        $emConfig->addCustomDatetimeFunction('DAY', 'DoctrineExtensions\Query\Mysql\Day');

        $qb = $this->createQueryBuilder('d');

        $qb->select('MONTH(d.dateSoumission) AS month, COUNT(d.id) AS count')
            ->where($qb->expr()->eq('YEAR(d.dateSoumission)', ':year'))
            ->setParameter('year', $year)
            ->groupBy('month')
            ->orderBy('month', 'ASC');

        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
    }


    public function getImpressionsByMonth(int $year): array
    {

        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('YEAR', 'DoctrineExtensions\Query\Mysql\Year');
        $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
        $emConfig->addCustomDatetimeFunction('DAY', 'DoctrineExtensions\Query\Mysql\Day');

        $qb = $this->createQueryBuilder('d')
            ->select('MONTH(d.dateDelivrance) as month, COUNT(d.id) as count')
            ->where('YEAR(d.dateDelivrance) = :year')
            ->setParameter('year', $year)
            ->groupBy('month')
            ->orderBy('month', 'ASC');

        return $qb->getQuery()->getResult();
    }


    public function getImpressionsInLast24Hours(): array
    {

        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('YEAR', 'DoctrineExtensions\Query\Mysql\Year');
        $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
        $emConfig->addCustomDatetimeFunction('DAY', 'DoctrineExtensions\Query\Mysql\Day');
        $emConfig->addCustomDatetimeFunction('HOUR', 'DoctrineExtensions\Query\Mysql\Your');

        $qb = $this->createQueryBuilder('d')
            ->select('HOUR(d.dateDelivrance) as hour, COUNT(d.id) as count')
            ->where('d.dateDelivrance >= :yesterday')
            ->setParameter('yesterday', new \DateTime('-24 hours'))
            ->groupBy('hour')
            ->orderBy('hour', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function getTotalImpressionsForCurrentYear(): int
    {
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('YEAR', 'DoctrineExtensions\Query\Mysql\Year');

        $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
        $emConfig->addCustomDatetimeFunction('DAY', 'DoctrineExtensions\Query\Mysql\Day');

        $currentYear = (new \DateTime())->format('Y');

        $qb = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('YEAR(d.dateDelivrance) = :currentYear')
            ->setParameter('currentYear', $currentYear);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getTotalImpressions(): int
    {
        $qb = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.dateDelivrance IS NOT NULL');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


    public function findValidCardForProfessionnel(int $professionnelId): ?Demande
    {
        $now = new DateTime(); // Date actuelle pour comparer avec la date d'expiration

        return $this->createQueryBuilder('d')
            ->where('d.professionnel = :professionnel')
            ->andWhere('d.isPrinted = 1')
            ->andWhere('d.dateExpiration >= :now')
            ->setParameter('professionnel', $professionnelId)
            ->setParameter('now', $now)
            ->orderBy('d.dateExpiration', 'DESC') // Trier par date d'expiration décroissante
            ->setMaxResults(1) // Récupérer uniquement la dernière demande
            ->getQuery()
            ->getOneOrNullResult();
    }


    //    /**
    //     * @return Demande[] Returns an array of Demande objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Demande
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
