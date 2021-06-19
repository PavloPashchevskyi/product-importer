<?php
declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\ProductData;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;

class ProductDataRepository extends ServiceEntityRepository
{
    /**
     * ProductDataRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductData::class);
    }

    /**
     * @param ProductData $entity
     * @throws ORMException
     */
    public function preSave(ProductData $entity): void
    {
        $this->_em->persist($entity);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(): void
    {
        $this->_em->flush();
    }

    /**
     * @param ProductData $entity
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function store(ProductData $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }
}
