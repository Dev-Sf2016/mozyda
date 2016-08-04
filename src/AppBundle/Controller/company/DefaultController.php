<?php

namespace AppBundle\Controller\company;

use AppBundle\Entity\Company;
use AppBundle\Entity\CompanyDelegate;
use AppBundle\Entity\Discount;
use AppBundle\Form\CompanyDelegateType;
use AppBundle\Form\CompanyType;
use AppBundle\Form\DiscountType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DefaultController extends Controller
{
    /**
     * @Route("/company/login", name="company_login")
     */
    public function loginAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {

            return $this->redirectToRoute('company_home');
        }
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        if ($error) {

            $request->getSession()
                ->getFlashBag()
                ->add('company_error', $error->getMessage());


            return $this->redirectToRoute('account_login');
        }
        return $this->render(
//            'company/login.html.twig',
            'default/accountlogin.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error' => $error,
            )
        );
    }

    /**
     * @Route("/company", defaults={"page": 1}, name="company_home")
     * @Route("/company/delegates/{page}", requirements={"page": "[1-9]\d*"}, name="company_home_delegates_paginated")
     */
    public function indexAction(Request $request, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $companyDelegateData = $em->getRepository('AppBundle:CompanyDelegate')->find($this->getUser()->getId());
        // add delegate functionality

        $companyDelegate = new CompanyDelegate();
//        var_dump($companyDelegateData);

        $formDeleg = $this->createForm(CompanyDelegateType::class, $companyDelegate);

        $formDeleg->handleRequest($request);

        if ($formDeleg->isSubmitted() && $formDeleg->isValid()) {

            $pass = $companyDelegate->getPassword();
            $companyDelegate->setPassword(md5($companyDelegate->getPassword()));

            $em = $this->getDoctrine()->getManager();


            $user = $em->getRepository('AppBundle:CompanyDelegate')->find($this->getUser()->getId());

            $companyDelegate->setCompany($user->getCompany());
            $companyDelegate->setIsDefault('0');

            $em->persist($companyDelegate);
            $em->flush();

            $this->addFlash('delegate_success', $this->get('translator')->trans('Delegate is added successfully'));
            $this->addFlash('tab', '2a');
//send email to newly created delegate
            $message = \Swift_Message::newInstance()
                ->setSubject($this->get('translator')->trans('Delegation Account created'))
                ->setFrom($this->getParameter("email_from"))
                ->setTo($companyDelegate->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/delegate-new-account-added.html.twig',
                        array(

                            'email' => $companyDelegate->getEmail(),
                            'name' => $companyDelegate->getName(),
                            'password' => $pass,
                            'company_name' => $user->getCompany()->getName(),
                            'url' => $this->generateUrl('account_login', array(), UrlGeneratorInterface::ABSOLUTE_URL)
                        )
                    )
                );


//                echo $message;
            $this->get('mailer')->send($message);

            return $this->redirectToRoute('company_home');
        }
        //end of add delegate functionality

        // add discount functionality

        $discount = new Discount();
        $discountForm = $this->createForm(DiscountType::class, $discount);

        $discountForm->handleRequest($request);
        if ($discountForm->isSubmitted() && $discountForm->isValid()) {
            if ($discountForm->getData()->getStartDate()->format('Y-m-d') > $discountForm->getData()->getEndDate()->format('Y-m-d')) {
                $error = new FormError($this->get('translator')->trans('Discount start date is greater then end date'));
                $discountForm->get('startDate')->addError($error);
            } else {
                $companyDelegateDisc = $em->getRepository('AppBundle:CompanyDelegate')->find($this->getUser()->getId());
                $file = $discount->getPromotion();
                $fileName = sprintf('%s-%s.%s', uniqid(), time(), $file->guessExtension());

                //check director exist or not
                $upload_dir = $this->getParameter("app.company.discount_path") . "/" . $companyDelegateDisc->getCompany()->getId();
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir);
                }
                // Move the file to the directory where brochures are stored

                $file->move(
                    $upload_dir,
                    $fileName
                );

                $discount->setPromotion($fileName);


                $discount->setCompany($companyDelegateDisc->getCompany());


                $em->persist($discount);

                $em->flush();

                $this->addFlash('discount_success', $this->get('translator')->trans('Discount added successfully'));
                $this->addFlash('tab', '3a');

//                $this->redirectToRoute('company_add_discount');
            }
        }
        if($request->get('_route') == 'company_home_delegates_paginated'){
            $this->addFlash('tab', '2a');
        }
        //end of add discount functionality
        $all_discounts = $this->companyDiscountList();
        $all_delegates = null;
        if ($companyDelegateData->getIsDefault() == 1) {
            $all_delegates = $this->getDoctrine()->getRepository('AppBundle:CompanyDelegate')->getDelegates($page, $companyDelegateData->getCompany());
        }

        return $this->render('company/default.html.twig', array(
            'company_delegate' => $companyDelegateData,
            'form_deleg' => $formDeleg->createView(),
            'form_discount' => $discountForm->createView(),
            'discounts' => $all_discounts['discounts'],
            'company_del' => $all_discounts['company'],
            'all_delegates' => $all_delegates
        ));
    }

    /**
     * @Route("company/logout/", name="company_logout")
     */
    public function logoutAction()
    {
    }

    /**
     * @Route("/registerCompany", name="company_register")
     * @param Request $request
     *
     * @return Response
     */
    public function registerCompanyAction(Request $request)
    {

        // echo $this->container->getParameter("app.company.logo_path");die();
        $company = new Company();
        $companyDelegate = new CompanyDelegate();
        $company->addCompanyDelegate($companyDelegate);
        $form = $this->createForm(CompanyType::class, $company);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

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

            $this->addFlash('success', $this->get('translator')->trans('Company is registered successfully'));

            return $this->redirectToRoute('company_register');
        }


        return $this->render(
            ':company:register-company.html.twig',
            array(
                'form' => $form->createView()
            ));
    }

    /**
     * @Route("/company/delegate/add", name="company_delegate_add")
     * @param Request $request
     *
     * @return Response
     */
    public function addCompanyDelegateAction(Request $request)
    {

        $companyDelegate = new CompanyDelegate();

        $form = $this->createForm(CompanyDelegateType::class, $companyDelegate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $companyDelegate->setPassword(md5($companyDelegate->getPassword()));

            $em = $this->getDoctrine()->getManager();

            $user = $em->getRepository('AppBundle:CompanyDelegate')->find($this->getUser()->getId());

            $companyDelegate->setCompany($user->getCompany());
            $companyDelegate->setIsDefault('1');

            $em->persist($companyDelegate);
            $em->flush();

            $this->addFlash('success', $this->get('translator')->trans('Delegate is added successfully'));

            return $this->redirectToRoute('company_delegate_add');
        }


        return $this->render(
            ':company:company-delegate-add.html.twig',
            array(
                'form' => $form->createView()
            ));
    }


    /**
     * @Route("/company/detail", name="company_detail")
     * @param Request $request
     *
     * @return Response
     */
    public function companyDetailAction(Request $request)
    {
        //var_dump($this->getUser()); die(); //->getCompany()->getId();die();
        $em = $this->getDoctrine()->getManager();
        $companyDelegate = $em->getRepository('AppBundle:CompanyDelegate')->find($this->getUser()->getId());


        return $this->render(
            ':company:company-detail.html.twig',
            array(
                'companyDelegate' => $companyDelegate
            ));
    }

    /**
     * @Route("/company/discount/{id}/delete", requirements={"id": "\d+"},name="company_delete_discount")
     *
     */
    public function deleteDiscountAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $coupon = $em->getRepository('AppBundle:Discount')->find($id);
        $em->initializeObject($coupon->getCompany());

        $user = $em->getRepository('AppBundle:CompanyDelegate')->find($this->getUser()->getId());
        $em->initializeObject($user->getCompany());
        if ($user->getCompany() == $coupon->getCompany()) {
            echo "valid";
            $em->remove($coupon);
            $em->flush();
            $this->addFlash('notice', $this->get('translator')->trans('Coupon Deleted Sucessfully'));
        }
        return $this->redirectToRoute('account_login');

//        $userCompany = $em->getRepository('AppBundle:Company')->findBy('')
    }
/**
     * @Route("/company/delegate/{id}/delete", requirements={"id": "\d+"},name="company_delete_delegate")
     *
     */
    public function deleteDelegateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $delegate = $em->getRepository('AppBundle:CompanyDelegate')->find($id);
        $em->initializeObject($delegate->getCompany());

        $user = $em->getRepository('AppBundle:CompanyDelegate')->find($this->getUser()->getId());
        $em->initializeObject($user->getCompany());
        if($delegate->getIsDefault() == 1){
            return $this->redirectToRoute('company_home');
        }
        if ($user->getCompany() == $delegate->getCompany()) {
            echo "valid";
            $em->remove($delegate);
            $em->flush();
            $this->addFlash('notice', $this->get('translator')->trans('Delegate Deleted Sucessfully'));
            $this->addFlash('tab', '2a');
        }
        return $this->redirectToRoute('company_home');

//        $userCompany = $em->getRepository('AppBundle:Company')->findBy('')
    }

    /**
     * @Route("/company/add/discount", name="company_add_discount")
     * @param Request $request
     *
     * @return Response
     */
    public function companyAddDiscountAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        //$company = $em->getRepository('AppBundle:Company')->find($this->getUser()->getCompany()->getId());

        $discount = new Discount();
        $form = $this->createForm(DiscountType::class, $discount, array(
            'validation_groups' => array('addnewonly')));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getData()->getStartDate()->format('Y-m-d') > $form->getData()->getEndDate()->format('Y-m-d')) {
                $error = new FormError($this->get('translator')->trans('Discount start date is greater then end date'));
                $form->get('startDate')->addError($error);
            } else {
                $companyDelegate = $em->getRepository('AppBundle:CompanyDelegate')->find($this->getUser()->getId());
                $file = $discount->getPromotion();
                $fileName = sprintf('%s-%s.%s', uniqid(), time(), $file->guessExtension());

                //check director exist or not
                $upload_dir = $this->getParameter("app.company.discount_path") . "/" . $companyDelegate->getCompany()->getId();
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir);
                }
                // Move the file to the directory where brochures are stored

                $file->move(
                    $upload_dir,
                    $fileName
                );

                $discount->setPromotion($fileName);


                $discount->setCompany($companyDelegate->getCompany());


                $em->persist($discount);

                $em->flush();

                $this->addFlash('message', $this->get('translator')->trans('Discount added successfully'));

                $this->redirectToRoute('company_add_discount');
            }

        } else {
            //var_dump($form->getErrors());die();
        }


        return $this->render(
            ':company:company-add-discount.html.twig',
            array(
                'form' => $form->createView()
            ));
    }

    /**
     * @Route("/company/discount/list", name="company_discount_list")
     * @param Request $request
     *
     * @return Response
     */
    public function companyDiscountList()
    {


        $em = $this->getDoctrine()->getManager();
        $companyDelegate = $em->getRepository('AppBundle:CompanyDelegate')->find($this->getUser()->getId());
        $em->initializeObject($companyDelegate->getCompany());
        $discounts = $em->getRepository('AppBundle:Discount')->getDiscountsByCompanyId($companyDelegate->getCompany());

        ///echo "salem";die();
        return array(
            'discounts' => $discounts,
            'company' => $companyDelegate
        );
    }

    /**
     * @Route("/company/discount/{id}/edit", requirements={"id": "\d+"},name="company_edit_discount")
     */
    public function companyDiscountEditAction(Discount $discount, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $existingImg = $discount->getPromotion();
        $form = $this->createForm(\AppBundle\Form\DiscountType::class, $discount);
        $form->handleRequest($request);
        $companyDelegate = $em->getRepository('AppBundle:CompanyDelegate')->find($this->getUser()->getId());
        if ($companyDelegate->getCompany() != $discount->getCompany()) {
            return $this->redirectToRoute('company_home');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($discount->getPromotion()) {
                $companyDelegate = $em->getRepository('AppBundle:CompanyDelegate')->find($this->getUser()->getId());
                $file = $discount->getPromotion();
                $fileName = sprintf('%s-%s.%s', uniqid(), time(), $file->guessExtension());
                //delete the old image

                if (file_exists($this->container->getParameter("app.company.discount_path") . '/' . $existingImg)) {
                    unlink($this->container->getParameter("app.company.discount_path") . '/' . $existingImg);
                }

                //check director exist or not
                $upload_dir = $this->getParameter("app.company.discount_path") . "/" . $companyDelegate->getCompany()->getId();
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir);
                }
                // Move the file to the directory where brochures are stored

                $file->move(
                    $upload_dir,
                    $fileName
                );

                $discount->setPromotion($fileName);
            } else {
                $discount->setPromotion($existingImg);
            }
            $em->persist($discount);
            $em->flush();
            $this->addFlash('notice', $this->get('translator')->trans('Coupon updated sucessfully'));
            $this->addFlash('tab', '4a');

            return $this->redirectToRoute('company_home');

        }
        return $this->render(
            ':company:company-edit-discount.html.twig',
            array(
                'form' => $form->createView(),
                'company_id' => $discount->getCompany()->getId()
            ));


    }


}