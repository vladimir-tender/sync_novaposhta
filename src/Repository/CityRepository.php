<?php

namespace App\Repository;


use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, City::class);
    }

    public function countCities(): int
    {
        $dql = 'SELECT COUNT(u) FROM App:City u ';
        $query = $this->getEntityManager()->createQuery($dql);
        try {
            $res = $query->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            $res = 0;
        }
        return intval($res);
    }
}
