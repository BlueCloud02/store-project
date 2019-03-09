<?php 
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Brand;
use App\Entity\Product;

class StoreController extends AbstractController {
	/**
     *
     * @Route("/store", name="store_index")
     */
	public function indexAction() {
		$em = $this->getDoctrine()->getManager();
        /** @var ProductRepository $productRepo */
        $productRepo = $em->getRepository(Product::class);
        /** @var BrandRepository $brandRepo */
        $brandRepo = $em->getRepository(Brand::class);

        /** @var Product[] $products */
        $products = $productRepo->findAll();
        /** @var Brand[] $brands */
        $brands = $brandRepo->findAll();
        
        return $this->render('store/index.html.twig', [
            'products' => $products,
            'brands' => $brands
        ]);
    }

    /**
     *
     * @Route("/ajax", name="store_ajaxSearch")
     */
	public function ajaxSearchAction(Request $request) {
		$getParams = $request->query;
        
        $em = $this->getDoctrine()->getManager();
        /** @var ProductRepository $productRepo */
        $productRepo = $em->getRepository(Product::class);
		
		/** @var Product[] $filteredProducts */
        $filteredProducts = $productRepo->filterProducts(
        	$getParams->get("search"),
        	json_decode($getParams->get("brands")), 
        	$getParams->get("saleNoticeDate") ? date("Y-m-d", strtotime($getParams->get("saleNoticeDate"))) : null, 
        	$getParams->get("maxPrice")
        );
		

        // Serialize products
        $response = array_map(function (array $row){
            /** @var Product $product */
            $product = $row['productEntity'];
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'reference' => $product->getReference(),
                'price' => $product->getPrice(),
                'saleNoticeDate' => $product->getSaleNoticeDate()->format('d-m-y'),
                'brand' => $product->getBrand()->getName()
            ];
        }, $filteredProducts);

        return $this->json($response);
    }
}
?>