<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    /**
     * Get the paginated list of products corresponding to the filters
     *
     */
    public function getFilteredProducts(int $page = 1, int $nbPerPage = 10, ?string $search, ?array $brandsIds, ?string $saleNoticeDate, ?int $maxPrice)
    {
        $qb = $this->createQueryBuilder('product');
        $qb->leftJoin('product.brand', 'brand');
    

        // If search bar is not empty
        if ($search) {
            // REFERENCE: if search string begins by a "#", it means that the search is a reference
            if($search[0]=='#'){ 
                $qb->andWhere('product.reference LIKE :ref')
                    ->setParameter('ref', "$search%");
            } 
            // PRICE: if the search is a price with the euro symbol "€"
            elseif ( strpos($search, '€') !== false) {
                $price = str_replace([' ','€'], '', $search); // Remove € symbol and spaces to get a float
                $price = floatval(str_replace(',', '.', $price)) ; // Get int price (Take care if user use the french way to have decimal with ",")
                
                if( $price !== 0){ // Floatval will return 0 if it does not recognize a float
                    $qb->andWhere("product.price BETWEEN :price-10 AND :price+10")  
                        ->setParameter('price', $price);  
                }
            } 
            // ELSE: search by name or brand
            else {
                $qb->andWhere('product.name LIKE :search')
                    ->orWhere('brand.name LIKE :search');
                $qb->setParameter('search', "%$search%");
            }

        }

        // If saleNoticeDate is set
        if($saleNoticeDate){
            $qb->andWhere('product.saleNoticeDate > :date')
                ->setParameter('date', $saleNoticeDate);
        }

        // If a maximum Price is set
        if(isset($maxPrice)) {
            $qb->andWhere('product.price < :expr')
                ->setParameter('expr', $maxPrice);
        }

        // If some brands are selected 
        if($brandsIds){
            $qb->andWhere('brand.id IN (:ids)')
                ->setParameter('ids', $brandsIds);
        }

        $qb->orderBy('product.id');

        // Execute request, get paginated result (10 products per page)
        $query = $qb->getQuery();
        $query->setFirstResult(($page-1)* $nbPerPage )
            ->setMaxResults($nbPerPage);
        return new Paginator($query, true);
    }
}
