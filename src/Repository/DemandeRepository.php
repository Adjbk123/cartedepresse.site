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
        // Création du QueryBuilder
        $qb = $this->createQueryBuilder('d')
            ->select('count(d.id)') // Sélection du nombre de demandes
            ->innerJoin('d.carte', 'c') // Jointure avec l'entité Carte
            ->where('c.dateExpiration < :today') // Condition sur la date d'expiration dans Carte
            ->setParameter('today', new \DateTime()) // Utilisation de la date actuelle
            ->andWhere('c.dateExpiration IS NOT NULL'); // Assurez-vous que la date d'expiration existe

        // Retourne le nombre d'enregistrements
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
        // Ajouter les fonctions personnalisées pour travailler avec les dates
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('YEAR', 'DoctrineExtensions\Query\Mysql\Year');
        $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
        $emConfig->addCustomDatetimeFunction('DAY', 'DoctrineExtensions\Query\Mysql\Day');

        // Requête pour récupérer les impressions par mois
        $qb = $this->createQueryBuilder('d')
            ->innerJoin('d.carte', 'c') // Jointure avec l'entité Carte
            ->select('MONTH(c.dateDelivrance) as month, COUNT(d.id) as count')
            ->where('YEAR(c.dateDelivrance) = :year') // Utilisation de la dateDelivrance de Carte
            ->setParameter('year', $year)
            ->groupBy('month')
            ->orderBy('month', 'ASC');

        // Exécuter la requête et retourner le résultat
        return $qb->getQuery()->getResult();
    }

    public function getImpressionsInLast24Hours(): array
    {
        // Ajouter les fonctions personnalisées pour travailler avec les dates
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('YEAR', 'DoctrineExtensions\Query\Mysql\Year');
        $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
        $emConfig->addCustomDatetimeFunction('DAY', 'DoctrineExtensions\Query\Mysql\Day');
        $emConfig->addCustomDatetimeFunction('HOUR', 'DoctrineExtensions\Query\Mysql\Hour'); // Correction de la fonction pour HOUR

        // Date de 24 heures avant l'heure actuelle
        $qb = $this->createQueryBuilder('d')
            ->innerJoin('d.carte', 'c') // Jointure avec l'entité Carte
            ->select('HOUR(c.dateDelivrance) as hour, COUNT(d.id) as count')
            ->where('c.dateDelivrance >= :yesterday')
            ->setParameter('yesterday', new \DateTime('-24 hours')) // Date il y a 24 heures
            ->groupBy('hour')
            ->orderBy('hour', 'ASC');

        // Exécuter la requête et retourner le résultat
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
            ->innerJoin('d.carte', 'c') // Jointure avec l'entité Carte
            ->where('YEAR(c.dateDelivrance) = :currentYear') // Utilisation de la dateDelivrance de la carte
            ->setParameter('currentYear', $currentYear);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countPrintedCards(): int
    {
        $qb = $this->createQueryBuilder('d')
            ->select('count(d.id)')
            ->innerJoin('d.carte', 'c') // Jointure avec la table Carte
            ->where('c.id IS NOT NULL'); // Vérifie que la demande a une carte associée

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


    public function findValidCardForProfessionnel(int $professionnelId): ?Demande
    {
        $now = new \DateTime(); // Date actuelle pour comparer avec la date d'expiration

        return $this->createQueryBuilder('d')
            ->innerJoin('d.carte', 'c') // Jointure avec l'entité Carte
            ->where('d.professionnel = :professionnel')
            ->andWhere('c.dateExpiration >= :now') // Vérifier si la date d'expiration de la carte est valide
            ->setParameter('professionnel', $professionnelId)
            ->setParameter('now', $now)
            ->orderBy('c.dateExpiration', 'DESC') // Trier par la date d'expiration décroissante de la carte
            ->setMaxResults(1) // Récupérer uniquement la dernière demande valide
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
