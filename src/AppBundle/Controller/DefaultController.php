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
            ->where('c.isActive = 1')
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(6)
            ->getQuery();

        $companies = $query->getresult();
        return $companies;
    }

    public function latestDiscounts()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $activeCompanies = $em->getRepository('AppBundle:Company')->findBy(
            array('isActive'=>1)
        );
        $repository = $this->getDoctrine()->getRepository('AppBundle:Discount');
        $query = $repository->createQueryBuilder('d')
            ->where('d.startDate <= :todaydate')
            ->andWhere('d.company IN (:active_companies)')
            ->andWhere('d.endDate >= :todaydate')
            ->orderBy('d.id', 'DESC')
            ->setMaxResults(4)
            ->setParameter('todaydate', date('Y-m-d'))
            ->setParameter('active_companies', $activeCompanies)
            ->getQuery();

        $discounts = $query->getresult();
        return $discounts;

    }
    /**
     * @Route("/samplemail", name="sample_mail")
     */
    public function samplemail(){
        return $this->render('/emails/sample.html.twig');
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
                case 'ROLE_ADMIN':
                    return $this->redirectToRoute('admin_home');
                    break;
                default:
                    break;
                endswitch;

        }
        // this function displays the form and check if user is already logged in then forward it to ccustomer or company page
        return $this->render('default/accountlogin.html.twig', array(
            )
        );
    }
}
