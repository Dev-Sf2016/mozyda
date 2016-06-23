<?php
namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ProductController extends Controller
{
    /**
     * @Route("/createprodcat")

        $category = new Category();
        $category->setName('Computer periferals');

        $product = new Product();
        $product->setName('Keyboard');
        $product->setPrice('19.99');
        $product->setDescription('keyboard description');

        $product->setCategory($category);

        $em = $this->getDoctrine()->getManager();
        $em->persist($category);
        $em->persist($product);
        $em->flush();

        return new Response(
            'saved prod with id'.$product->getId()
            .'new category is '.$category->getId()

        );
         */
}
?>