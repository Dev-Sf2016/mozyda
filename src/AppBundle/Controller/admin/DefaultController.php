<?php

namespace AppBundle\Controller\admin;

use AppBundle\AppBundle;
use AppBundle\Entity\Company;
use AppBundle\Entity\Customer;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;

class DefaultController extends Controller
{
    function __construct()
    {
//        var_dump($this->getUser());
//        die('');
    }

    /**
     * @Route("/admin/login", name="admin_login")
     */
    public function loginAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {

            return $this->redirectToRoute('admin_home');
        }
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error' => $error,
            )
        );
    }


    /**
     * @Route("/admin/", name="admin_home")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('admin/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..'),
        ]);
    }

    /**
     * @Route("admin/logout/", name="admin_logout")
     */
    public function logoutAction()
    {
    }

    /**
     * @Route("/admin/companies", defaults={"page": 1}, name="admin_companies")
     * @Route("admin/companies/{page}", requirements={"page": "[1-9]\d*"}, name="admin_companies_paginated")
     * @Method("GET")
     * @Cache(smaxage="10")
     */
    public function companiesAction($page)
    {
        $companies = $this->getDoctrine()->getRepository('AppBundle:Company')->getCompanies($page);
        return $this->render(
            'admin/index/companies.html.twig',
            array(
                'companies' => $companies
            )
        );
    }

    /**
     * @Route("/admin/customers", defaults={"page": 1}, name="admin_customers")
     * @Route("admin/customers/{page}", requirements={"page": "[1-9]\d*"}, name="admin_customers_paginated")
     * @Method("GET")
     * @Cache(smaxage="10")
     */
    public function customersAction($page)
    {
        $customers = $this->getDoctrine()->getRepository('AppBundle:Customer')->getCustomers($page);
        return $this->render(
            'admin/index/customers.html.twig',
            array(
                'customers' => $customers
            )
        );
    }

    /**
     * @Route("admin/custoemr/{id}/edit", requirements={"id": "\d+"}, name="admin_customer_edit")
     * @Method({"GET", "POST"})
     */
    public function customerEditAction(Customer $customer, Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $form = $this->createForm(\AppBundle\Form\CustRegForm::class, $customer);
        $currentPass = $customer->getPassword();
        $currentLoyalityId = $customer->getLoyalityId();
        $form->handleRequest($request);
//        var_dump($customer);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($customer->getPassword() == '') {
                $customer->setPassword($currentPass);
            } else {
                $customer->setPassword(md5($customer->getPassword()));
            }
            //if form is valid we need to check the loyality number by api and in the db if it is already registered

            $saveForm = true;
            if ($currentLoyalityId <> $customer->getLoyalityId()) {
                $loyality = $this->get('app.loyality');
                $cardStatus = $loyality->checkLoyalityCard($customer->getLoyalityId());

                if (!$cardStatus['status']) {
                    $error = new FormError($cardStatus['msg']);
                    $form->get('loyality_id')->addError($error);
                    $saveForm = false;

                }
            }
            if ($saveForm) {

                $em->persist($customer);
                $em->flush();

                $this->addFlash('notice', $this->get('translator')->trans('Customer Updated Sucessfully'));
                return $this->redirectToRoute('admin_customer_edit', array('id' => $customer->getId()));
            }
        }
        return $this->render(
            'admin/index/customeredit.html.twig',
            array(
                'form' => $form->createView()
            )
        );

    }


    /**
     * Displays a form to edit an existing user vendor entity.
     *
     * @Route("admin/company/{id}/edit", requirements={"id": "\d+"}, name="admin_company_edit")
     * @Method({"GET", "POST"})
     */
    public function companyEditAction(Company $company, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
//        var_dump($company);
        $logoFilename = $company->getLogo();
        $existingLogo = $company->getLogo();
        $oldStatus = $company->getIsActive();
        $editForm = $this->createForm(\AppBundle\Form\CompanyEdit::class, $company);
        $editForm->handleRequest($request);
        $deleteCompanyForm = $this->createCompanyDeleteForm($company);
//        if ($company->getLogo() == '') {
//            $company->setLogo(
//                new File($this->container->getParameter("app.company.logo_path") . '/' . $existingLogo));
//        }

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            if ($company->getLogo()) {

                // delete the old thumbmail image

                if (file_exists($this->container->getParameter("app.company.logo_path") . '/' . $logoFilename)) {
                    unlink($this->container->getParameter("app.company.logo_path") . '/' . $logoFilename);
                }
                $logo = $company->getLogo();
                $logoFilename = sprintf('%s-%s.%s', uniqid(), time(), $logo->guessExtension());
                $logo->move(
                    $this->container->getParameter("app.company.logo_path") . '/',
                    $logoFilename
                );
            }

            $company->setLogo($logoFilename);

            $em->persist($company);
            $em->flush();
            // if company is being enabled send email to default delegate
            if($oldStatus == '0' && $company->getIsActive() == 1){
                $companyDelegate = $em->getRepository('AppBundle:CompanyDelegate')->findOneBy(
                    array('company' => $company,
                        'isDefault' => 1)
                );
                $message = \Swift_Message::newInstance()
                    ->setSubject($this->get('translator')->trans('Company Account Activation at Mzaaya.com'))
                    ->setFrom($this->getParameter("email_from"))
                    ->setTo($companyDelegate->getEmail())
                    ->setBody(
                        $this->renderView(
                            'emails/company-activation.html.twig',
                            array(

                                'name' => $company->getName()
                            )
                        ),
                        'text/html'
                    );

//                echo $message;
                $this->get('mailer')->send($message);
            }

            $this->addFlash('notice', $this->get('translator')->trans('Company Updated Sucessfully'));
            return $this->redirectToRoute('admin_company_edit', array('id' => $company->getId()));
        }
        // draw the default delegation form
        $em = $this->getDoctrine()->getManager();
        $companyDelegate = $em->getRepository('AppBundle:CompanyDelegate')->findOneBy(
            array('company' => $company,
                'isDefault' => 1)
        );
        $currentPass = $companyDelegate->getPassword();
        $formDelegation = $this->createFormBuilder($companyDelegate, array('attr' => array('novalidate' => 'novalidate')))
            ->setAction($this->get('router')->getGenerator()->generate('admin_company_edit', array('id' => $company->getId())))
            ->add('name', TextType::class, array('label' => 'Delegate Name'))
            ->add('email', EmailType::class, array('label' => 'Email'))
            ->add('password', PasswordType::class)->getForm()
            ->add('save', SubmitType::class, array('label' => 'Save', 'attr' => array("class" => 'btn btn-custom btn-lg btn-block')));
        $formDelegation->handleRequest($request);
        if ($formDelegation->isSubmitted() && $formDelegation->isValid()) {
            echo "inside submission the posted data is ";
            var_dump($companyDelegate);
            if ($companyDelegate->getPassword() == '') {
                $companyDelegate->setPassword($currentPass);
            } else {
                $companyDelegate->setPassword(md5($companyDelegate->getPassword()));
            }
            $em->persist($companyDelegate);
            $em->flush();

            $this->addFlash('notice', $this->get('translator')->trans('Delegation Updated Sucessfully'));
            return $this->redirectToRoute('admin_company_edit', array('id' => $company->getId()));
        } else {
            echo "form is not valid";
        }
        return $this->render(
            'admin/index/companyedit.html.twig',
            array(
                'company' => $company,
                'form' => $editForm->createView(),
                'file_path' => $this->container->getParameter("app.company.logo_path"),
                'existing_logo' => $existingLogo,
                'form_delegation' => $formDelegation->createView(),
                'delete_company_form' => $deleteCompanyForm->createView()
            ));
    }

    /**
     * @Route("/{id}", name="admin_company_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Company $company)
    {
        $form = $this->createCompanyDeleteForm($company);
        $form->handleRequest($request);
        $logoFileName = $company->getLogo();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            //delete all delegates
            $delegates = $entityManager->getRepository('AppBundle:CompanyDelegate')->findBy(
                array('company' => $company)
            );
            foreach ($delegates as $delegate) {
                $entityManager->remove($delegate);
                $entityManager->flush();

            }
            if ($logoFileName <> '') {
                if (file_exists($this->container->getParameter("app.company.logo_path") . '/' . $logoFileName)) {
                    unlink($this->container->getParameter("app.company.logo_path") . '/' . $logoFileName);
                }
            }
            $entityManager->remove($company);
            $entityManager->flush();

            $this->addFlash('notice', $this->get('translator')->trans('Company Deleted Sucessfully'));
        }

        return $this->redirectToRoute('admin_companies');
    }

    private function createCompanyDeleteForm(Company $company)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_company_delete', array('id' => $company->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }


}
