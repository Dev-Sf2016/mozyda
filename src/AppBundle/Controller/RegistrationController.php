<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Company;
use AppBundle\Entity\CompanyDelegate;
use AppBundle\Form\CompanyType;
use AppBundle\Form\CustRegForm;
use AppBundle\Entity\Customer;
use AppBundle\Security\User\CustomerUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Generator\UrlGenerator;

class RegistrationController extends Controller
{

    /**
     * @Route("/register", defaults={"inv_id": 0}, name="register")
     * @Route("/register/{inv_id}/", requirements={"inv_id": "[1-9]\d*"}, name="register_invited")
     */
    public function register(Request $request, $inv_id)
    {
//        echo "invitation id is ".$inv_id;
        $em = $this->getDoctrine()->getManager();
        // if there is inv_id display the invitor email in the box
        // register customer
        $customer = new Customer();

        $formCust = $this->createForm(CustRegForm::class, $customer);
        if ($inv_id != 0) { // purpose is only to show the invitor email in the refferer email box from invitations table
            $inv_by = $em->getRepository('AppBundle:CustomerInvitation')->findOneBy(array('id' => $inv_id));
            $inv_by_details = $em->getRepository('AppBundle:Customer')->findOneBy(array('id' => $inv_by->getRefferer()));
            $formCust->get('refferer_email')->setData($inv_by_details->getEmail());
            $formCust->get('email')->setData($inv_by->getEmail());

        }

        $formCust->handleRequest($request);
        //because it is expecting the entity to implement userinterface
        $customerUser = new CustomerUser($customer->getEmail(), $customer->getPassword(), $customer->getName(), $customer->getLoyalityId(), $customer->getCity(), $customer->getNationality(), $customer->getIsActive());
        $success = '';
        if ($formCust->isSubmitted() && $formCust->isValid()) {
            $referrer_email = '';

            if ($formCust['refferer_email']->getData() != '') {
                $referrer_email = $formCust['refferer_email']->getData();
            }

            //if form is valid we need to check the loyality number by api and in the db if it is already registered
            $cardStatus = $this->checkLoyalityCard($customer->getLoyalityId());

            $saveForm = true;
            if (!$cardStatus['status']) {
                $error = new FormError($cardStatus['msg']);
                $formCust->get('loyality_id')->addError($error);
                $saveForm = false;

            }
            if ($referrer_email != '') {
                $reffererInfo = $this->getRefferer($referrer_email);
                if ($reffererInfo['error'] != '') {
                    $error = new FormError($reffererInfo['error']);
                    $formCust->get('refferer_email')->addError($error);
                    $saveForm = false;
                }else{

                    $customer->setRefferedBy($reffererInfo['refferer']);
                }
            }
            if ($saveForm) {
                $password = $this->get('security.password_encoder')
                    ->encodePassword($customerUser, $customer->getPassword());
                $customer->setPassword($password);
                $customer->setPassword($password);
//                $em = $this->getDoctrine()->getManager();
                $customer->setIsActive(0);
                $customer->setActivationCode(date('himsymd') . rand(0, 3000));


                $em->persist($customer);
                $em->flush();

                // send code via email to user to activate
                $message = \Swift_Message::newInstance()
                    ->setSubject($this->get('translator')->trans('Account Activation'))
                    ->setFrom('send@example.com')
                    ->setTo($customer->getEmail())
                    ->setBody(
                        $this->renderView(
                            'emails/registration.html.twig',
                            array('id' => $customer->getId(), 'code' => $customer->getActivationCode())
                        ),
                        'text/html'
                    );

//                echo $message;
        $this->get('mailer')->send($message);


//                $this->get('session')->getFlashBag()->add('notice', 'You have registered Sucessfully, Please follow the email to activate your account');
                $this->addFlash('success_cust', $this->get('translator')->trans('You have registered Sucessfully, Please follow the email to activate your account'));
            }
        } else {
        }
        //end of register customer

        // register company
        $company = new Company();
        $companyDelegate = new CompanyDelegate();
        $company->addCompanyDelegate($companyDelegate);
        $formComp = $this->createForm(CompanyType::class, $company);

        $formComp->handleRequest($request);


        if ($formComp->isSubmitted() && $formComp->isValid()) {

            $file = $company->getLogo();
            $fileName = sprintf('%s-%s.%s', uniqid(), time(), $file->guessExtension());

            // Move the file to the directory where brochures are stored
            $file->move(

                $this->container->getParameter("app.company.logo_path"),
                $fileName
            );

            $company->setLogo($fileName);
            $company->setIsActive(0);
            $em = $this->getDoctrine()->getManager();

            $em->persist($company);

            $companyDelegate->setPassword(md5($companyDelegate->getPassword()));
            $companyDelegate->setIsDefault('1');

            $companyDelegate->setCompany($company);
            $em->persist($companyDelegate);
            $em->flush();

            $this->addFlash('success_comp', $this->get('translator')->trans('Company is registered successfully'));

//            return $this->redirectToRoute('company_register');
        }
        // end of register company
        return $this->render(
            'registration/registration.html.twig',
            array(
                'formCust' => $formCust->createView(),
                'formComp' => $formComp->createView(),
//                'success' => $success
            )
        );
    }

    //function to fetch refferer id from referrer email
    public function getRefferer($email)
    {

        $em = $this->getDoctrine()->getManager();
        $refferer = $em->getRepository('AppBundle:Customer')->findOneBy(
            array(
                'email' => $email
            ));
        if (!empty($refferer)) {
            $arr = array('refferer_id' => $refferer->getId(), 'error' => '','refferer'=>$refferer);
        } else {
            $arr = array('refferer_id' => '', 'error' => $this->get('translator')->trans('Invalid refferer email'));

        }
        return $arr;


    }

    /**
     * @Route("/registerCustomer", name="customer_registration")
     */

    public function customerRegistrationAction(Request $request)
    {
        $customer = new Customer();
        $form = $this->createForm(CustRegForm::class, $customer);
        $form->handleRequest($request);
        //because it is expecting the entity to implement userinterface
        $customerUser = new CustomerUser($customer->getEmail(), $customer->getPassword(), $customer->getName(), $customer->getLoyalityId(), $customer->getCity(), $customer->getNationality(), $customer->getIsActive());
        $success = '';
        if ($form->isSubmitted() && $form->isValid()) {
            //if form is valid we need to check the loyality number by api and in the db if it is already registered
            $cardStatus = $this->checkLoyalityCard($customer->getLoyalityId());
            if (!$cardStatus['status']) {
                $error = new FormError($cardStatus['msg']);
                $form->get('loyality_id')->addError($error);

            } else {
                $password = $this->get('security.password_encoder')
                    ->encodePassword($customerUser, $customer->getPassword());
                $customer->setPassword($password);
                $password = '000';
                $customer->setPassword($password);
                $em = $this->getDoctrine()->getManager();
                $customer->setIsActive(0);
                $customer->setActivationCode(date('himsymd') . rand(0, 3000));
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
        } else {
            $string = (string)$form->getErrors(true, false);
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
        if (!$user) {
            // this card is not registered
            // check from POS service
            $loyality = $this->get('app.loyality');
            $cardStatus = $loyality->checkCardStatus($id);
            if ($cardStatus) {
                return array('status' => true, 'msg' => $this->get('translator')->trans('OK'));
            } else {
                return array('status' => false, 'msg' => $this->get('translator')->trans("Invalid Card Number"));
            }

        } else {
            return array('status' => false, 'msg' => $this->get('translator')->trans("Card already registered"));
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
        if (!$customer) {
            throw $this->createNotFoundException(
                'Invalid user'
            );
        }
//        var_dump($customer);
        $account_active = 0;
        if ($customer->getIsActive() == 1) {
            $account_active = 1;
            $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('Your Account is already in active state'));
        } else {
            if ($customer->getActivationCode() == $code) {
                $customer->setIsActive(1);
                $customer->setActivationCode('');
                $em->flush();
                $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('Your Account is Active you can login now'));
            } else {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('Invalid activation code'));
            }

        }
        return $this->redirectToRoute('register');
//        return $this->render(
//            'registration/customeractivation.html.twig',
//            array(
//                'account_active' => $account_active
//            ));
    }

    /**
     * @Route("/forgot/customer/password", name="forgot_customer_password")
     * @param Request $request
     *
     * @return Response
     */
    public function forgotCustomerPasswordAction(Request $request){

//        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
//            return $this->redirectToRoute('homepage');
//        }

        $form = $this->createFormBuilder(null, array('attr'=>array('novalidate'=>'novalidate')))
            //->setAttribute('novalidate', 'novalidate')
            ->setAction($this->get('router')->getGenerator()->generate('forgot_customer_password'))
            ->add('email', RepeatedType::class, array(
                'invalid_message'=>'Email and confirm email must match',
                'first_options'=> array('label'=>'Enter Your email'),
                'second_options'=> array('label'=>'Confirm your email'),
                'constraints'=>array(
                    new Assert\NotBlank(),
                    new Assert\Email()
                ),
                'type'=>TextType::class
            ))
            ->add('save', SubmitType::class, array('label'=>'submit'))
            ->getForm();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //save the form
            $formData = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $customer = $em->getRepository('AppBundle:Customer')->findOneBy(array("email"=>$formData['email']));

            if($customer == null){
                $this->addFlash('error', $this->get('translator')->trans('User does not exist'));
            }
            else{

                //$token = hash('sha256', uniqid(mt_rand(), true), true);
                $time = time();
                $token =   uniqid() . md5($formData['email'] . time() . rand(111111, 999999));
                $link = $this->generateUrl('reset_customer_password', array('time'=>$time, 'token'=>$token), UrlGenerator::ABSOLUTE_URL);

                $customer->setData(serialize(array('time'=>$time, 'token'=>$token)));

                $em->persist($customer);
                $em->flush();

                //Send email to the use
                $message = \Swift_Message::newInstance()
                    ->setSubject($this->get('translator')->trans('Forgot Password'))
                    ->setFrom($this->getParameter('email_from'))
                    ->setTo($formData['email'])
                    ->setBody(
                        $this->renderView(
                            'emails/forgot-customer-password-email.twig',
                            array(
                                'name'=>$customer->getName(),
                                'link'=>$link
                            )
                        ),
                        'text/html'
                    );
                $this->get('mailer')->send($message);

                $this->addFlash('success', $this->get('translator')->trans('An email is send to you please check your inbox.'));
            }



//            return $this->redirectToRoute('forgot_customer_password');
        }
        return $this->render(
            'registration/forgot-customer-password.html.twig',
            array(
                'form'=>$form->createView()
            )
        );
    }

    /**
     * @Route("/forgot/company/password", name="forgot_company_password")
     * @param Request $request
     *
     * @return Response
     */
    public function forgotCompanyPasswordAction(Request $request){

//        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
//            return $this->redirectToRoute('homepage');
//        }

        $form = $this->createFormBuilder(null, array('attr'=>array('novalidate'=>'novalidate')))
            //->setAttribute('novalidate', 'novalidate')
            ->setAction($this->get('router')->getGenerator()->generate('forgot_company_password'))
            ->add('email', RepeatedType::class, array(
                'invalid_message'=>'Email and confirm email must match',
                'first_options'=> array('label'=>'Enter Your email'),
                'second_options'=> array('label'=>'Confirm your email'),
                'constraints'=>array(
                    new Assert\NotBlank(),
                    new Assert\Email()
                ),
                'type'=>TextType::class
            ))
            ->add('save', SubmitType::class, array('label'=>'submit'))
            ->getForm();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //save the form
            $formData = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $companyDel = $em->getRepository('AppBundle:CompanyDelegate')->findOneBy(array("email"=>$formData['email']));

            if($companyDel == null){
                $this->addFlash('error', $this->get('translator')->trans('User does not exist'));
            }
            else{

                //$token = hash('sha256', uniqid(mt_rand(), true), true);
                $time = time();
                $token =   uniqid() . md5($formData['email'] . time() . rand(111111, 999999));
                $link = $this->generateUrl('reset_company_password', array('time'=>$time, 'token'=>$token), UrlGenerator::ABSOLUTE_URL);

                $companyDel->setData(serialize(array('time'=>$time, 'token'=>$token)));

                $em->persist($companyDel);
                $em->flush();

                //Send email to the use
                $message = \Swift_Message::newInstance()
                    ->setSubject($this->get('translator')->trans('Forgot Password'))
                    ->setFrom($this->getParameter('email_from'))
                    ->setTo($formData['email'])
                    ->setBody(
                        $this->renderView(
                            'emails/forgot-customer-password-email.twig',
                            array(
                                'name'=>$companyDel->getName(),
                                'link'=>$link
                            )
                        ),
                        'text/html'
                    );
//                echo $message;
                $this->get('mailer')->send($message);

                $this->addFlash('success', $this->get('translator')->trans('An email is send to you please check your inbox.'));
            }



//            return $this->redirectToRoute('forgot_company_password');
        }
        return $this->render(
            'registration/forgot-company-password.html.twig',
            array(
                'form'=>$form->createView()
            )
        );
    }


    /**
     * @Route("/reset/customer/password/{time}/{token}", name="reset_customer_password")
     * @param Request $request
     * @param $time
     * @param $token
     *
     * @return Response
     */
    public function resetCustomerPasswordAction(Request $request, $time, $token){

//        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
//            return $this->redirectToRoute('homepage');
//        }
        $viewData = array();
        $em = $this->getDoctrine()->getManager();
        $time = (integer)$time;
        $dataValue = serialize(array('time'=>$time, 'token'=>$token));
        $customer = $em->getRepository('AppBundle:Customer')->findOneBy(array("data"=>$dataValue));

        if($customer == null){
            return $this->redirectToRoute('homepage');
        }
        else{
            $data = unserialize($customer->getData());
            if(strtotime('+1 day', $data['time']) > time()){
                $form = $this->createFormBuilder(array('attr'=>array('novalidate'=>'novalidate')))
                    ->add('password', RepeatedType::class, array(
                        'type'=>PasswordType::class,
                        'constraints' => array(
                            new Assert\NotBlank(),
                            new Assert\Length(array('minMessage'=>'Password must be at least 8 characters', 'maxMessage'=>'Password must not be greater then 40 characters', 'min'=>8, 'max'=>40))
                        ),
                        'invalid_message'=>'Password and confirm password must match',
                        'first_options'=>array('label'=>'Enter New Password'),
                        'second_options'=>array('label'=>'Confirm Password'),
                        'second_name'=>'confirmPassword'
                    ))
                    ->add('submit', SubmitType::class, array('label'=>'Submit'))
                    ->getForm();

                $form->handleRequest($request);
                if($form->isSubmitted() && $form->isValid()){

                    $formData = $form->getData();

                    $customer->setPassword(md5($formData['password']));
                    $customer->setData('');
                    $em->persist($customer);
                    $em->flush();
                    $email_message = \Swift_Message::newInstance()
                        ->setSubject($this->get('translator')->trans('Password Changed'))
                        ->setTo($customer->getEmail())
                        ->setFrom($this->getParameter("email_from"))
                        ->setBody(
                            $this->renderView(
                                'emails\reset-customer-password-email.twig',
                                array(
                                    'name'=>$customer->getName(),
                                    'password'=>$formData['password']
                                )
                            ),
                            'text/html'
                        );

                    $this->get('mailer')->send($email_message);
                    $this->addFlash('success', $this->get('translator')->trans('Your password is reset successfully'));




                }

                $viewData['form'] = $form->createView();
            }
            else{
                $this->addFlash('error', $this->get('translator')->trans('Link expired'));
            }
        }

        return $this->render(
            'registration/reset-customer-password.html.twig',
            $viewData
        );
    }

    /**
     * @Route("/reset/company/password/{time}/{token}", name="reset_company_password")
     * @param Request $request
     * @param $time
     * @param $token
     *
     * @return Response
     */
    public function resetCompanyPasswordAction(Request $request, $time, $token){

//        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
//            return $this->redirectToRoute('homepage');
//        }
        $viewData = array();
        $em = $this->getDoctrine()->getManager();
        $time = (integer)$time;
        $dataValue = serialize(array('time'=>$time, 'token'=>$token));
        $companyDel = $em->getRepository('AppBundle:CompanyDelegate')->findOneBy(array("data"=>$dataValue));

        if($companyDel == null){
            return $this->redirectToRoute('homepage');
        }
        else{
            $data = unserialize($companyDel->getData());
            if(strtotime('+1 day', $data['time']) > time()){
                $form = $this->createFormBuilder(array('attr'=>array('novalidate'=>'novalidate')))
                    ->add('password', RepeatedType::class, array(
                        'type'=>PasswordType::class,
                        'constraints' => array(
                            new Assert\NotBlank(),
                            new Assert\Length(array('minMessage'=>'Password must be at least 8 characters', 'maxMessage'=>'Password must not be greater then 40 characters', 'min'=>8, 'max'=>40))
                        ),
                        'invalid_message'=>'Password and confirm password must match',
                        'first_options'=>array('label'=>'Enter New Password'),
                        'second_options'=>array('label'=>'Confirm Password'),
                        'second_name'=>'confirmPassword'
                    ))
                    ->add('submit', SubmitType::class, array('label'=>'Submit'))
                    ->getForm();

                $form->handleRequest($request);
                if($form->isSubmitted() && $form->isValid()){

                    $formData = $form->getData();

                    $companyDel->setPassword(md5($formData['password']));
                    $companyDel->setData('');
                    $em->persist($companyDel);
                    $em->flush();
                    $email_message = \Swift_Message::newInstance()
                        ->setSubject($this->get('translator')->trans('Password Changed'))
                        ->setTo($companyDel->getEmail())
                        ->setFrom($this->getParameter("email_from"))
                        ->setBody(
                            $this->renderView(
                                'emails\reset-company-password-email.twig',
                                array(
                                    'name'=>$companyDel->getName(),
                                    'password'=>$formData['password']
                                )
                            ),
                            'text/html'
                        );

                    $this->get('mailer')->send($email_message);
                    $this->addFlash('success', $this->get('translator')->trans('Your password is reset successfully'));




                }

                $viewData['form'] = $form->createView();
            }
            else{
                $this->addFlash('error', $this->get('translator')->trans('Link expired'));
            }
        }

        return $this->render(
            'registration/reset-company-password.html.twig',
            $viewData
        );
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
    public function testresetAction()
    {
        $loyality = $this->get('app.loyality');
        $points = $loyality->getPoints(150);

        echo "points are <pre>";
        print_r($points);
        echo "</pre>";
        return new Response("<html></html>");
    }
}

?>