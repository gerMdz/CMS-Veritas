<?php

namespace App\Repository;

use App\Entity\Reservante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reservante|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservante|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservante[]    findAll()
 * @method Reservante[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservante::class);
    }

    // /**
    //  * @return Reservante[] Returns an array of Reservante objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /**
     * @param array $celebraciones
     * @return array|null
     */
    public function faltanDatosInvitadosFromReservante(array $celebraciones): ?array
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('r');
        $qb->leftJoin('r.invitados', 'i');
        $qb->andWhere(
            $qb->expr()->in('r.celebracion', ':celebraciones')
        )->setParameter(':celebraciones', $celebraciones);

        $qb->andWhere(
            'i.email is Null and i.dni is Null and i.nombre is Null and i.apellido is Null'
        );
        $qb->addGroupBy('r');
        return $qb->getQuery()->getArrayResult();
    }

    public function findOneByReserva($celebracion, $email): ?Reservante
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.celebracion = :cel')
            ->andWhere('r.email = :email')
            ->setParameter('cel', $celebracion)
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

}
