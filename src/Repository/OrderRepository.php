<?php

namespace App\Repository;

use App\DTO\OrderFilterDto;
use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function save(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(int $id, bool $flush = false): void
    {
        $entity = $this->find($id);
        if ($entity) {
            $this->getEntityManager()->remove($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }
    }

    public function findWithFilters(OrderFilterDto $filters): array
    {
        $qb = $this->createQueryBuilder('o');

        // filters

        if ($filters->status) {
            $qb->andWhere('o.status = :status')
                ->setParameter('status', OrderStatus::from($filters->status));
        }

        if ($filters->email) {
            $qb->andWhere('o.customer_email LIKE :email')
                ->setParameter('email', '%' . $filters->email . '%');
        }

        if ($filters->date_from) {
            $qb->andWhere('o.created_at >= :date_from')
                ->setParameter('date_from', new \DateTimeImmutable($filters->date_from . ' 00:00:00'));
        }

        if ($filters->date_to) {
            $qb->andWhere('o.created_at <= :date_to')
                ->setParameter('date_to', new \DateTimeImmutable($filters->date_to . ' 23:59:59'));
        }

        $qb->orderBy('o.created_at', 'DESC');

        // pagination
        $offset = ($filters->page - 1) * $filters->limit;
        $qb->setFirstResult($offset)
            ->setMaxResults($filters->limit);

        $paginator = new Paginator($qb->getQuery());
        $total = count($paginator);
        $orders = iterator_to_array($paginator);

        return [
            'orders' => $orders,
            'total' => $total,
            'page' => $filters->page,
            'limit' => $filters->limit,
            'total_pages' => ceil($total / $filters->limit)
        ];
    }

    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
