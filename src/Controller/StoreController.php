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
     *
     */
	public function indexAction(Request $request) {
        $getParams = $request->query;
        $nbPerPage = 10;
        $page = $getParams->get("page") ?? 1;
        
        // GET PRODUCTS AND BRANDS
		$em = $this->getDoctrine()->getManager();
        /** @var ProductRepository $productRepo */
        $productRepo = $em->getRepository(Product::class);
        /** @var BrandRepository $brandRepo */
        $brandRepo = $em->getRepository(Brand::class);

        /** @var Product[] $products */
        $products = $productRepo->getFilteredProducts(
            $page, 
            $nbPerPage, 
            $getParams->get("search"),
            json_decode($getParams->get("brands")), 
            $getParams->get("saleNoticeDate") ? date("Y-m-d", strtotime($getParams->get("saleNoticeDate"))) : null, 
            $getParams->get("maxPrice")
        ); 

        /** @var Brand[] $brands */
        $brands = $brandRepo->findAll();
        
        // FOR PAGINATION
        $nbPages = ceil(count($products) / $nbPerPage);
        if($page > $nbPages && count($products) != 0){
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        return $this->render('store/index.html.twig', [
            'products' => $products,
            'brands' => $brands,
            'nbPages' => $nbPages,
            'page' => $page
        ]);
    }


    /**
     *
     * @Route("/ajax", name="store_ajaxSearch")
     *
     */
	public function ajaxSearchAction(Request $request, int $page = 1) {
		$getParams = $request->query;
        $nbPerPage = 10;
        $page = $getParams->get("page") ?? 1;
        
        // GET PRODUCTS
        $em = $this->getDoctrine()->getManager();
        /** @var ProductRepository $productRepo */
        $productRepo = $em->getRepository(Product::class);
		
		/** @var Product[] $filteredProducts */
        $filteredProducts = $productRepo->getFilteredProducts(
            $page,
            $nbPerPage,
        	$getParams->get("search"),
        	json_decode($getParams->get("brands")), 
        	$getParams->get("saleNoticeDate") ? date("Y-m-d", strtotime($getParams->get("saleNoticeDate"))) : null, 
        	$getParams->get("maxPrice")
        );
		

        // SERIALIZE PRODUCTS
        $response = [];

        foreach($filteredProducts as $product) {
            $response['products'][] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'reference' => $product->getReference(),
                'price' => $product->getPrice(),
                'saleNoticeDate' => $product->getSaleNoticeDate()->format('d-m-y'),
                'brand' => $product->getBrand()->getName()
            ];
        }   

        // PAGINATION
        $response['nbPages'] = ceil(count($filteredProducts) / $nbPerPage);
        $response['nbProducts'] = count($filteredProducts);

        return $this->json($response);
    }
}
?>