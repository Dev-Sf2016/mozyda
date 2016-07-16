<?php
namespace AppBundle\Controller\home;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * @Route("/allStores", defaults={"page": 1}, name="list_stores")
     * @Route("allStores/{page}", requirements={"page": "[1-9]\d*"}, name="list_stores_paginated")
     * @Method("GET")
     * @Cache(smaxage="10")
     */
    public function allStoresAction($page){
        $companies = $this->getDoctrine()->getRepository('AppBundle:Company')->getCompaniesActive($page);
        return $this->render(
            'home/allstores.html.twig',
            array(
                'companies' => $companies
            )
        );
    }

    /**
     * @Route("/allDiscounts", defaults={"page": 1}, name="all_discounts")
     * @Route("allDiscounts/{page}", requirements={"page": "[1-9]\d*"}, name="all_discounts_paginated")
     * @Method("GET")
     * @Cache(smaxage="10")
     */
    public function allDiscountsAction($page){
        $em = $this->getDoctrine()->getManager();
        $discounts = $this->getDoctrine()->getRepository('AppBundle:Discount')->getDiscounts($page);
        return $this->render(
            'home/alldiscounts.html.twig',
            array(
                'discounts' => $discounts
            )
        );
    }
    /**
     * @Route("/allStoreDiscounts/{store}", defaults={"page": 1}, name="all_store_discounts")
     * @Route("allStoreDiscounts/{store}/{page}", requirements={"page": "[1-9]\d*"}, name="all_store_discounts_paginated")
     * @Method("GET")
     * @Cache(smaxage="10")
     */
    public function allStoreDiscountsAction($store=null,$page=null){
        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('AppBundle:Company')->find($store);
        $discounts = $this->getDoctrine()->getRepository('AppBundle:Discount')->getDiscounts($page,$company);
        return $this->render(
            'home/allstorediscounts.html.twig',
            array(
                'company' => $company,
                'discounts' => $discounts
            )
        );
    }
}
?>