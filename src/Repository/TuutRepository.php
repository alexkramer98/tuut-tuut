<?php

namespace App\Repository;

use App\Entity\Tuut;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tuut|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tuut|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tuut[]    findAll()
 * @method Tuut[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TuutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tuut::class);
    }

    // /**
    //  * @return Tuut[] Returns an array of Tuut objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Tuut
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
