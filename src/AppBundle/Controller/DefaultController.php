<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $companies = $this->companies();
        return $this->render('default/index.html.twig', array(
            'companies' => $companies
            )
        );
    }

    public function companies()
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Company');
        $query = $repository->createQueryBuilder('c')
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(6)
            ->getQuery();

        $companies = $query->getresult();
        return $companies;
    }
   
}
