<?php

namespace App\Repository;

use App\Entity\ModelTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ModelTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModelTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModelTemplate[]    findAll()
 * @method ModelTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModelTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModelTemplate::class);
    }

    /**
     * @return QueryBuilder Returns an array of ModelTemplate objects
     */

    public function findByTypeSection()
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.block', 'b')
            ->andWhere('b.identifier = :val')
            ->setParameter('val', 'seccion' )
            ->orderBy('m.description', 'ASC')
//            ->setMaxResults(10)
 //           ->getQuery()
//            ->getResult()
        ;
    }

    /**
     * @return QueryBuilder Returns an array of ModelTemplate objects
     */

    public function findByTypeEntrada()
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.block', 'b')
            ->andWhere('b.identifier = :val')
            ->setParameter('val', 'entrada' )
            ->orderBy('m.description', 'ASC')
//            ->setMaxResults(10)
            //           ->getQuery()
//            ->getResult()
            ;
    }


    /*
    public function findOneBySomeField($value): ?ModelTemplate
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
