<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function filterProducts(?string $search, ?array $brandsIds, ?string $saleNoticeDate, ?int $maxPrice)
    {
        $qb = $this->createQueryBuilder('product');
        $qb->select('product as productEntity');
        
        // If some brands are selected 
        if($brandsIds){
            $qb->leftJoin('product.brand', 'brand')
                ->where('brand.id IN (:ids)')
                ->setParameter('ids', $brandsIds);
        }

        // If search bar is not empty
        if ($search) {
            if($search[0]=='#'){ // if search string begins by a "#", it means that the search is a reference
                $qb->andWhere('product.reference LIKE :search');
            } else {
                $qb->andWhere('product.name LIKE :search');    
            }
            $qb->setParameter('search', "%$search%");

        }

        // If saleNoticeDate is set
        if($saleNoticeDate){
            $qb->andWhere('product.saleNoticeDate > :date')
                ->setParameter('date', $saleNoticeDate);
        }

        // If a maximum Price is set
        if($maxPrice) {
            $qb->andWhere('product.price < :expr')
                ->setParameter('expr', $maxPrice);
        }

        // Execute request, get result
        $query = $qb->getQuery();
        return $query->getResult();
    }
}
