<?php
namespace AppBundle\Controller;

use AppBundle\Form\CustRegForm;
use AppBundle\Entity\Customer;
use AppBundle\Security\User\CustomerUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;

class RegistrationController extends Controller
{

    /**
     * @Route("/registerCustomer", name="customer_registration")
     */

    public function customerRegistrationAction(Request $request)
    {
        $customer = new Customer();
        $form = $this->createForm(CustRegForm::class, $customer);
        $form->handleRequest($request);
        //because it is expecting the entity to implement userinterface
        $customerUser = new CustomerUser($customer->getEmail(), $customer->getPassword(),$customer->getName(),$customer->getLoyalityId(),$customer->getCity(),$customer->getNationality(),$customer->getIsActive());
        $success = '';
        if ($form->isSubmitted() && $form->isValid()) {
            //if form is valid we need to check the loyality number by api and in the db if it is already registered
            $cardStatus = $this->checkLoyalityCard($customer->getLoyalityId());
            if(!$cardStatus['status']){
                $error = new FormError($cardStatus['msg']);
                $form->get('loyality_id')->addError($error);

            }else {
                $password = $this->get('security.password_encoder')
                    ->encodePassword($customerUser, $customer->getPassword());
                $customer->setPassword($password);
                $password = '000';
                $customer->setPassword($password);
                $em = $this->getDoctrine()->getManager();
                $customer->setIsActive(0);
                $customer->setActivationCode(date('himsymd').rand(0,3000));
                $em = $this->getDoctrine()->getManager();

                $em->persist($customer);
                $em->flush();

                // send code via email to user to activate
                $message = \Swift_Message::newInstance()
                    ->setSubject('Account Activation')
                    ->setFrom('send@example.com')
                    ->setTo($customer->getEmail())
                    ->setBody(
                        $this->renderView(
                        // app/Resources/views/Emails/registration.html.twig
                            'emails/registration.html.twig',
                            array('id' => $customer->getId(), 'code' => $customer->getActivationCode())
                        ),
                        'text/html'
                    );

                echo $message;
//        $this->get('mailer')->send($message);


                $this->get('session')->getFlashBag()->add('notice', 'You have registered Sucessfully, Please follow the email to activate your account');
            }
        }else{
            $string = (string) $form->getErrors(true, false);
            echo $string;
        }



        return $this->render(
            'registration/customerregistration.html.twig',
            array('form' => $form->createView(),
                'success' => $success)
        );
    }
    public function checkLoyalityCard($id)
    {
        // check if loyality card is alreayd registered to a user in our website db
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:Customer')->findOneBy(
            array('loyalityId' => $id)
        );
        if(!$user){
            // this card is not registered
            // check from POS service
            $loyality = $this->get('app.loyality');
            $cardStatus = $loyality->checkCardStatus($id);
            if($cardStatus){
                return array('status'=>true, 'msg'=>'OK');
            }else{
                return array('status'=>false, 'msg'=>"Invalid Card Number");
            }

        }else{
            return array('status'=>false, 'msg'=>"card already registered");
        }
    }

    /**
     * @Route("/activateCustomer/{id}/{code}", name="customer_activation")
     */
    public function activateCustomer($id, $code)
    {
        $em = $this->getDoctrine()->getManager();
        $customer = $em->getRepository('AppBundle:Customer')->findOneBy(
            array('id' => $id)
        );
        if(!$customer){
            throw $this->createNotFoundException(
                'Invalid user'
            );
        }
        var_dump($customer);
        $account_active = 0;
        if($customer->getIsActive() == 1){
            $account_active = 1;
            $this->get('session')->getFlashBag()->add('notice', 'Your Account is already in active state');
        }else {
            if ($customer->getActivationCode() == $code) {
                $customer->setIsActive(1);
                $customer->setActivationCode('');
                $em->flush();
                $this->get('session')->getFlashBag()->add('notice', 'Your Account is Active you can login now');
            }else{
                $this->get('session')->getFlashBag()->add('error', 'Invalid activation code');
            }

        }

        return $this->render(
            'registration/customeractivation.html.twig',
            array(
                'account_active' => $account_active
            ));
    }

    /**
     * @Route("/vendorRegistration", name="vendor_registration")
     */

    public function vendorRegistrationAction(Request $request)
    {

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $success = '';
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $em = $this->getDoctrine()->getManager();
            $vendor = $em->getRepository('AppBundle:UserType')->find(2);
            $user->setUserType($vendor);
            $user->setIsActive(0);
            $user->setVerificationCode(date('himsymd'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'Your changes were saved!');
        }



        return $this->render(
            'registration/vendorregistration.html.twig',
            array('form' => $form->createView(),
                'success' => $success)
        );
    }
    /**
     * @Route("/testrest", name="test_rest")
     */
    public function testresetAction(){
       $loyality = $this->get('app.loyality');
        $points =  $loyality->getPoints(150);

        echo "points are <pre>"; print_r($points); echo "</pre>";
        return new Response("<html></html>");
    }
}

?>