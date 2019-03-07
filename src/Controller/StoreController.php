<?php 
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class StoreController extends AbstractController {
	/**
     *
     * @Route("/store", name="store_index")
     */
	public function indexAction() {
		return $this->render('store/index.html.twig');
	}
}
?>