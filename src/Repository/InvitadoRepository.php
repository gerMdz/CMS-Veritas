<?php

namespace App\Repository;

use App\Entity\Invitado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Invitado|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invitado|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invitado[]    findAll()
 * @method Invitado[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitadoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invitado::class);
    }

    // /**
    //  * @return Invitado[] Returns an array of Invitado objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */


    /**
     * @param string $value
     * @return int|mixed|string
     */
    public function countByCelebracion(string $value)
    {
        try {
            return $this->createQueryBuilder('i')
                ->select('count(i.id) as ocupado')
                ->andWhere('i.celebracion = :val')
                ->setParameter('val', $value)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            return $e;
        } catch (NonUniqueResultException $e) {
            return $e;
        }

    }

    public function findOneByReserva($invitado): ?Invitado
    {
        return $this->createQueryBuilder('i')
            ->select('i')
            ->andWhere('i.id = :invitado')
//            ->andWhere('i.email = :email')
            ->setParameter('invitado', $invitado)
//            ->setParameter('email', $email)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string|null $celebracion
     * @param string|null $qSearch
     * @return QueryBuilder
     */
    public function searchBuilder(?string $celebracion, ?string $qSearch): QueryBuilder
    {
        $qb = $this->createQueryBuilder('i');
        if ($celebracion) {
            $qb->andWhere('i.celebracion = :celebracion')
                ->setParameter(':celebracion', $celebracion);
        }
        if ($qSearch) {
            $qb->andWhere('i.apellido LIKE :qsearch OR i.nombre LIKE :qsearch OR i.email LIKE :qsearch')
                ->setParameter('qsearch', '%' . $qSearch . '%');
        }

        return $qb;
    }


    /**
     * @param array $celebraciones
     * @return QueryBuilder
     */
    public function faltanDatosInvitados(array $celebraciones): QueryBuilder
    {
        $qb = $this->createQueryBuilder('i');
        $qb->andWhere(
            $qb->expr()->in('i.celebracion', ':celebraciones')
        )->setParameter(':celebraciones', $celebraciones);

        $qb->andWhere(
            'i.email is Null and i.dni is Null and i.nombre is Null and i.apellido is Null'
        );
        return $qb;
    }

    /**
     * @param string|null $celebracion
     * @param string|null $qSearch
     * @return QueryBuilder
     */
    public function searchQueryBuilder(?string $celebracion, ?string $qSearch): QueryBuilder
    {
        $qb = $this->searchBuilder($celebracion, $qSearch);
        return $qb
            ->addOrderBy('i.createdAt', 'DESC')
            ->addOrderBy('i.apellido', 'DESC');

    }

    public function byCelebracionForExport($celebracion)
    {
        $qb = $this->searchBuilder($celebracion, null)
            ->select('i.id as id, i.isPresente as presente,CONCAT(i.nombre, \'  \' , i.apellido) as invits, i.telefono as WhatsApp, i.dni as documento, i.email as email, i.updatedAt as reserva, i.isEnlace as reservante, CONCAT(invitante.nombre,\' \', invitante.apellido) as invito ');
        $qb->leftJoin('i.enlace', 'invitante');
        $qb->addOrderBy('invitante.apellido', 'ASC');
        $qb->addOrderBy('i.isEnlace', 'DESC');
        return $qb->getQuery()
            ->getArrayResult();
    }

    public function getInvitadosByCelebracion($celebracion)
    {
        return $this->createQueryBuilder('i')
            ->select('i')
            ->andWhere('i.celebracion = :celebracion')
            ->setParameter(':celebracion', $celebracion);

    }

    public function getAusentesCelebracion($celebracion)
    {
        return $this->getInvitadosByCelebracion($celebracion)
            ->andWhere('i.isPresente is null')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $celebracion
     * @return int|mixed|string
     */
    public function countAusentesByCelebracion(string $celebracion)
    {
        try {
            return $this->createQueryBuilder('i')
                ->select('count(i.id) as ausentes')
                ->andWhere('i.isPresente = :val')
                ->andWhere('i.celebracion = :cel')
                ->setParameter('val', false)
                ->setParameter('cel', $celebracion)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            return $e;
        } catch (NonUniqueResultException $e) {
            return $e;
        }

    }

    /**
     * @param string $enlace
     * @return int|mixed|string
     */
    public function countInvitadorByReservante(string $enlace)
    {
        try {
            return $this->createQueryBuilder('i')
                ->select('count(i.id) as invitados')
                ->andWhere('i.enlace = :enlace')
                ->setParameter('enlace', $enlace)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return $e;
        }

    }

    /**
     * @param $celebracion
     * @param $email
     * @return int|mixed|string
     */
    public function findOneByCelebracionEmail($celebracion, $email)
    {

        return $this->createQueryBuilder('i')
            ->andWhere('i.celebracion = :cel')
            ->andWhere('i.email = :email')
            ->setParameter('cel', $celebracion)
            ->setParameter('email', $email)
            ->getQuery()
            ->getResult();

    }

}
