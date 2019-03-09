<?php 
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
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
}
?>