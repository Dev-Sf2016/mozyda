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
     * @Route("/company", name="company_home")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $companyDelegateData = $em->getRepository('AppBundle:CompanyDelegate')->find($this->getUser()->getId());
//        var_dump($companyDelegate);
        // add delegate functionality

        $companyDelegate = new CompanyDelegate();

        $formDeleg = $this->createForm(CompanyDelegateType::class, $companyDelegate);

        $formDeleg->handleRequest($request);

        if ($formDeleg->isSubmitted() && $formDeleg->isValid()) {


            $companyDelegate->setPassword(md5($companyDelegate->getPassword()));

            $em = $this->getDoctrine()->getManager();

            $user = $em->getRepository('AppBundle:CompanyDelegate')->find($this->getUser()->getId());

            $companyDelegate->setCompany($user->getCompany());
            $companyDelegate->setIsDefault('1');

            $em->persist($companyDelegate);
            $em->flush();

            $this->addFlash('delegate_success', $this->get('translator')->trans('Delegate is added successfully'));
            $this->addFlash('tab', '2a');

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
        //end of add discount functionality
        $all_discounts = $this->companyDiscountList();

        return $this->render('company/default.html.twig', array(
            'company_delegate' => $companyDelegateData,
            'form_deleg' => $formDeleg->createView(),
            'form_discount' => $discountForm->createView(),
            'discounts' => $all_discounts['discounts'],
            'company_del' => $all_discounts['company']
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
        $form = $this->createForm(DiscountType::class, $discount);

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
//        echo "<br />current logged in user is".$this->getUser()->getId()."<br />";
//        var_dump($companyDelegate);
        $discounts = $em->getRepository('AppBundle:Discount')->getDiscountsByCompanyId($companyDelegate->getCompany());

        ///echo "salem";die();
        return array(
            'discounts' =>$discounts,
            'company' =>$companyDelegate
        );
//        return $this->render(
//            ':company:company-list-discount.html.twig',
//            array(
//                'company' => $companyDelegate->getCompany(),
//                'discounts' => $discounts
//            ));
    }


}