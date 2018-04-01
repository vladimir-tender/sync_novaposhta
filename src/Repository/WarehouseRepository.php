<?php

namespace App\Repository;


use App\Entity\Warehouse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

class WarehouseRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Warehouse::class);
    }

    public function countWarehouses(): int
    {
        $dql = 'SELECT COUNT(w) FROM App:Warehouse w ';
        $query = $this->getEntityManager()->createQuery($dql);
        try {
            $res = $query->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            $res = 0;
        }
        return intval($res);
    }
}
