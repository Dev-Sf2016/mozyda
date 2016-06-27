<?php

namespace AppBundle\Controller\company;

use AppBundle\Entity\Company;
use AppBundle\Entity\CompanyDelegate;
use AppBundle\Form\CompanyDelegateType;
use AppBundle\Form\CompanyType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("company/login", name="company_login")
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

        return $this->render(
            'company/login.html.twig',
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

        return $this->render('company/default.html.twig', array(

        ));
    }
    /**
     * @Route("company/logout/", name="company_logout")
     */
    public function logoutAction(){
    }

    /**
     * @Route("/company/register", name="company_register")
     * @param Request $request
     *
     * @return Response
     */
    public function registerCompanyAction(Request $request){

       // echo $this->container->getParameter("app.company.logo_path");die();
        $company = new Company();
        $companyDelegate = new CompanyDelegate();
        $company->addCompanyDelegate($companyDelegate);
        $form = $this->createForm(CompanyType::class, $company);

         $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

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

            $companyDelegate->setPassword( md5($companyDelegate->getPassword()));
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
    public function addCompanyDelegateAction(Request $request){

        $companyDelegate = new CompanyDelegate();

        $form = $this->createForm(CompanyDelegateType::class, $companyDelegate);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){


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
    public function companyDetailAction(Request $request){
        
        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('AppBundle:Company')->find($this->getUser()->getCompany()->getId());

        
        return $this->render(
            ':company:company-delegate-add.html.twig',
            array(
                'company' =>$company
            ));
    }

    /**
     * @Route("/company/upload/discount", name="company_upload_discount")
     * @param Request $request
     *
     * @return Response
     */
    public function companyUploadDiscountAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('AppBundle:Company')->find($this->getUser()->getCompany()->getId());


        return $this->render(
            ':company:company-delegate-add.html.twig',
            array(
                'form' => $company
            ));
    }

    /**
     * @Route("/company/discount/list", name="company_discount_list")
     * @param Request $request
     *
     * @return Response
     */
    public function companyDiscountListAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('AppBundle:Company')->find($this->getUser()->getCompany()->getId());


        return $this->render(
            ':company:company-delegate-add.html.twig',
            array(
                'form' => $company
            ));
    }
}