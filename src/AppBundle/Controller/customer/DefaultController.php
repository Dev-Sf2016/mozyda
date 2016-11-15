<?php
namespace AppBundle\Controller\customer;

use AppBundle\Entity\Customer;
use AppBundle\Form\CustRegForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function __construct()
    {


    }

    /**
     * @Route("/customer", name="customer_home")
     */
    public function indexAction(Request $request)
    {


        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $customer = $em->getRepository('AppBundle:Customer')->findOneBy(
            array('id' => $user->getId())
        );
        $currentPass = $customer->getPassword();
        $form = $this->createForm(CustRegForm::class, $customer);
//        $em->initializeObject($userData->getCustomerInvitations());
//        $em->initializeObject($userData->getCustomerRefferalPointsHistory());
//        var_dump($userData->getCustomerRefferalPointsHistory());
//        die('--');
        $loyalityId = $user->getLoyalityId();
        $loyality = $this->get('app.loyality');
        $points = $loyality->getPoints($loyalityId);
        $custInfo = $loyality->getCustInfo($loyalityId);
        $custTransStats = $loyality->getCustTransStats($loyalityId);
        $custTrans = $loyality->getTransHistory($loyalityId);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($customer->getPassword() == '') {
                $customer->setPassword($currentPass);
            } else {
                $customer->setPassword(md5($customer->getPassword()));
            }
            $em->persist($customer);
            $em->flush();
            $this->addFlash('notice', $this->get('translator')->trans('Customer Updated Sucessfully'));
            $this->addFlash('tab', '3a');
            return $this->redirectToRoute('customer_home');
            //if form is valid we need to check the loyality number by api and in the db if it is already registered

        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('tab', '3a');
        }
//        // replace this example code with whatever you need

        return $this->render('customer/default.html.twig',
            [
                'points' => $points,
                'cust_info' => $custInfo,
                'cust_trans' => $custTrans,
                'cust_trans_stats' => $custTransStats,
                'form' => $form->createView()
            ]);
    }

    /**
     * @Route("customer/login", name="customer_login")
     */
    public function loginAction(Request $request)
    {
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
        if ($error) {

            $request->getSession()
                ->getFlashBag()
                ->add('cust_error', $error->getMessage());


            return $this->redirectToRoute('account_login');
        }
        return $this->render(
//            'customer/login.html.twig',
            'default/accountlogin.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error' => $error,
            )
        );
    }

    /**
     * @Route("customer/logout/", name="customer_logout")
     */
    public function logoutAction()
    {
    }

}

?>