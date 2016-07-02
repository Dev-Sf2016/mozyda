<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Date;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $companies = $this->companies();
        $discounts = $this->latestDiscounts();
//        var_dump($discounts);
        return $this->render('default/index.html.twig', array(
                'companies' => $companies,
                'discounts' => $discounts
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

    public function latestDiscounts()
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Discount');
        $query = $repository->createQueryBuilder('d')
            ->where('d.startDate <= :todaydate')
            ->andWhere('d.endDate >= :todaydate')
            ->orderBy('d.id', 'DESC')
            ->setMaxResults(4)
            ->setParameter('todaydate', date('Y-m-d'))
            ->getQuery();

        //echo $query->getSQL()."<br>";var_dump($query->getParameter('todaydate'));die();
        $discounts = $query->getresult();
        return $discounts;
    }


    /**
     * @Route("/accountLogin",name="account_login")
     *
     */
    public function accountLogin()
    {
        $security_context = $this->get('session')->get('_security_my_context');
        if($security_context != null){
            $security_context = unserialize($security_context);
            $roles = $security_context->getUser()->getRoles();
            switch ($roles[0]):
                case 'ROLE_COMPANY':
                    return $this->redirectToRoute('company_home');
                    break;
                case 'ROLE_CUSTOMER':
                    return $this->redirectToRoute('customer_home');
                    break;
                default:
                    break;
                endswitch;

        }
        // this function displays the form and check if user is already logged in then forward it to ccustomer or company page
        echo "000";
        var_dump($this->getUser());
        return $this->render('default/accountlogin.html.twig', array(
            )
        );
    }
}
