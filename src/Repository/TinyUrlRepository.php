<?php

namespace App\Repository;

use App\Entity\TinyUrl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TinyUrl|null find($id, $lockMode = null, $lockVersion = null)
 * @method TinyUrl|null findOneBy(array $criteria, array $orderBy = null)
 * @method TinyUrl[]    findAll()
 * @method TinyUrl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TinyUrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TinyUrl::class);
    }

    // /**
    //  * @return TinyUrl[] Returns an array of TinyUrl objects
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
    public function findOneBySomeField($value): ?TinyUrl
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
