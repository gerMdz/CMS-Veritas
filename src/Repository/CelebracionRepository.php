<?php

namespace App\Repository;

use App\Entity\Celebracion;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Celebracion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Celebracion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Celebracion[]    findAll()
 * @method Celebracion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CelebracionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Celebracion::class);
    }

    public function findAllByFechaCelebracion()
    {
        return $this->getOrCreateQueryBuilder()
            ->orderBy('c.fechaCelebracionAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

     /**
      * @return QueryBuilder Returns an array of Celebracion objects
      */

    public function puedeMostrarse(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isHabilitada = :hab')
            ->andWhere('c.disponibleAt <= :today')
            ->andWhere('c.disponibleHastaAt >= :today')
            ->orderBy('c.fechaCelebracionAt', 'ASC')
            ->setParameter('hab', true)
            ->setParameter('today',new DateTime('now'))
        ;
    }

    public function puedeAgruparse(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isHabilitada = :hab')
            ->andWhere('c.disponibleHastaAt >= :today')
            ->orderBy('c.fechaCelebracionAt', 'ASC')
            ->setParameter('hab', true)
            ->setParameter('today',new DateTime('now'))
        ;
    }


    /*
    public function findOneBySomeField($value): ?Celebracion
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    private function sumar1hora()
    {
        $datetime = new DateTime('now');
        $datetime->modify('+1 hour');
        return $datetime;
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null)
    {
        return $qb ?: $this->createQueryBuilder('c');
    }

    public static function createIsPresenteCriteria():Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('isPresente', true))
            ;
    }
}
