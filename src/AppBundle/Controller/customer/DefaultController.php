<?php
namespace AppBundle\Controller\customer;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/customer", name="customer_home")
     */
    public function indexAction(Request $request)
    {

//        $user = $this->getUser();
//        $loyalityId = $user->getLoyalityId();
//        $loyality = $this->get('app.loyality');
//        $points =  $loyality->getPoints($loyalityId);
//        $custInfo = $loyality->getCustInfo($loyalityId);
//        $custTrans = $loyality->getCustTransStats($loyalityId);
//        // replace this example code with whatever you need

        echo "this is customer index action";
        return $this->render('customer/default.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
//            'points' => $points,
//            'cust_info' => $custInfo,
//            'cust_trans' => $custTrans
        ]);
    }

    /**
     * @Route("customer/login", name="customer_login")
     */
    public function loginAction(Request $request){
        //var_dump(unserialize($_SESSION['_sf2_attributes']['_security_member_area']));die();
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('customer_home');
        }


        $authenticationUtils = $this->get('security.authentication_utils');
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

//        return $this->render(
//            ':front/index:login.html.twig',
//            array(
//                // last username entered by the user
//                'last_username' => $lastUsername,
//                'error' => $error,
//
//            )
//        );

        return $this->render(
            'customer/login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
            )
        );
    }
    /**
     * @Route("customer/logout/", name="customer_logout")
     */
    public function logoutAction(){
    }

}
?>