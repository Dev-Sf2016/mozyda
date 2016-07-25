<?php

namespace AppBundle\Controller\admin;

use AppBundle\Entity\Company;
use AppBundle\Entity\Customer;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
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
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){

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
                'error'         => $error,
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
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * @Route("admin/logout/", name="admin_logout")
     */
    public function logoutAction(){
    }

    /**
     * @Route("/admin/companies", defaults={"page": 1}, name="admin_companies")
     * @Route("admin/companies/{page}", requirements={"page": "[1-9]\d*"}, name="admin_companies_paginated")
     * @Method("GET")
     * @Cache(smaxage="10")
     */
    public function companiesAction($page){
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
    public function customersAction($page){
        $customers = $this->getDoctrine()->getRepository('AppBundle:Customer')->getCustomers($page);
        return $this->render(
            'admin/index/customers.html.twig',
            array(
                'customers' => $customers
            )
        );
    }
    /**
     * Displays a form to edit an existing user vendor entity.
     *
     * @Route("admin/company/{id}/edit", requirements={"id": "\d+"}, name="admin_company_edit")
     * @Method({"GET", "POST"})
     */
    public function vendoreditAction(Company $company, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $editForm = $this->createForm(\AppBundle\Form\CompanyEdit::class, $company);
        $deleteForm = $this->createDeleteForm($company);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
//            die('form is submitted');
            $em->persist($company);
            $em->flush();
            $this->addFlash('notice', $this->get('translator')->trans('Company Deleted Sucessfully'));
            return $this->redirectToRoute('admin_customers');
        }
        return $this->render(
            'admin/index/vendoredit.html.twig',
            array(
                'company' => $company,
                'form' => $editForm->createView(),
                'delete_form' => $deleteForm->createView()
            ));
    }

    /**
     * @Route("admin/user/delete/{id}", name="admin_user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, User $user)
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('notice', 'User Deleted Sucessfully');
        }
        return $this->redirectToRoute('admin_vendors');
    }

    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }

    
    


}
