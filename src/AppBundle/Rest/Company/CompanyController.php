<?php

namespace AppBundle\Rest\Company;


use AppBundle\Entity\Company;
use AppBundle\Entity\CompanyDelegate;
use AppBundle\Exception\InvalidFormException;
use AppBundle\Form\CompanyDelegateType;
use AppBundle\Form\CompanyType;
use AppBundle\Rest\Codes;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class CompanyController
 * @package AppBundle\Controller\Rest\Company
 * @RouteResource("company", pluralize=false)
 */
class CompanyController extends FOSRestController
{
    private function isAllowed()
    {
        if($this->get('security.token_storage')->getToken()->area == 'company'){
            return true;
        }

        return false;
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($id){

        if(!$this->isAllowed()){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }
        $companyRepository = $this->getDoctrine()->getRepository('AppBundle:Company');
        $delegateRepository = $this->getDoctrine()->getRepository('AppBundle:CompanyDelegate');
        $company = null;
        $delegate = null;
        try{
            $company = $companyRepository->find($id);
            $delegate = $delegateRepository->findDefaultDelegateByCompany($company);

        }
        catch (\Exception $e){
            $company = null;
            $delegate = null;
        }

        if(!$company){
            $view = $this->view(['message'=>$this->get('translator')->trans('Record not found')], Codes::HTTP_NOT_FOUND );
        }
        else {

            $view = $this->view(['delegate'=>$delegate, 'base_url'=>'http://mozyda.dev'],  Codes::HTTP_OK );
        }

        return $this->handleView($view);
    }

    /**
     *
     */
    public function cgetAction(){
        $companyRepository = $this->getDoctrine()->getRepository('AppBundle:Company');
        $companies = null;
        $companies = $companyRepository->findAll();

        if(!$companies){
            $view = $this->view(['message'=>$this->get('translator')->trans('Records not found')], Codes::HTTP_NOT_FOUND);
        }
        else {
            $view = $this->view(['companies' => $companies], Codes::HTTP_OK);
        }

        return $this->handleView($view);


    }

    public function newtAction(Request $request){

    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction(Request $request){
        //$companyRepository = $this->getDoctrine()->getRepository('AppBundle:Company');

        try{
            $company = $this->createNewType($request);

            $routeOptions = [
                'id'=>$company->getId(),
                '_format'=>$request->get('_format')
            ];

            $this->redirectView('api_get_company', $routeOptions, Codes::HTTP_CREATED);
        }
        catch(InvalidFormException $e){
            $view = $this->view(['form'=>$e->getForm()], Codes::HTTP_BADE_REQUEST);
            return $this->handleView($view);
        }



    }

    /**
     * @param Request $request
     * @return Company|mixed
     */
    private function createNewType(Request $request){
        $parameters = $request->request->all();
        $company = new Company();

        $companyDelegate = new CompanyDelegate();
        $company->addCompanyDelegate($companyDelegate);

        $persistedCompany = $this->processForm($company, $parameters, 'POST');

        return $persistedCompany;


    }

    /**
     * @param Company $company
     * @param array $parameters
     * @param string $method
     * @return Company|mixed
     */
    private function processForm(Company $company, array $parameters, $method='PUT'){
        $form = $this->createForm(CompanyType::class, $company, ['method'=>$method, 'csrf_protection'=>false] );
        $form->submit($parameters, 'PATCH' !== $method);

        if($form->isValid()){
            $company = $form->getData();

            $companyRepository = $this->getDoctrine()->getRepository('AppBundle:Company');
            $companyRepository->persistCompany($company);

            return $company;
        }

        throw new InvalidFormException($this->get('translator')->trans('Invalid Submitted data'), $form);

    }

    /**
     * @param Request $request
     * @param $id
     * @return \FOS\RestBundle\View\View
     */
    public function patchAction(Request $request, $id){

        try{
            $company = $this->getDoctrine()->getRepository("AppBundle:Company")->find($id);

            if(!$company){
                throw new NotFoundHttpException($this->get('translator')->trans('The resource \'%s\' was not found', $id));
            }

            $statusCode = Codes::HTTP_NO_CONTENT;
            $company = $this->processForm($company, $request->request->all(), 'PATCH');

            $routeOptions = [
                'id' => $id,
                '_format' => $request->get('_format')
            ];

            return $this->routeRedirectView('api_get_company', $routeOptions, $statusCode);

        }
        catch(InvalidFormException $e){

        }
    }

    /**
     * @param $cid
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDiscountAction($cid, $id){
        $discountRepository = $this->getDoctrine()->getRepository('AppBundle:Discount');

        $discount = null;
        try{
            $discount = $discountRepository->find($id);
        }
        catch (\Exception $e){
            $discount = null;
        }

        if(!$discount){
            $view = $this->view(['message'=>$this->get('translator')->trans('Record not found')], Codes::HTTP_NOT_FOUND );
        }
        else {
            $view = $this->view(['discount'=> $discount],  Codes::HTTP_OK );
        }

        return $this->handleView($view);
    }


    /**
     * @param $cid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetDiscountAction($cid){

        $discountsRepository = $this->getDoctrine()->getRepository('AppBundle:Discount');

        $discounts = $discountsRepository->findAll();

        if(!$discounts){
            $view = $this->view(['message'=>$this->get('translator')->trans('Records not found')], Codes::HTTP_NOT_FOUND);
        }
        else {
            $view = $this->view(['discounts' => $discounts, 'base_url'=>'http://mozyda.dev/files/company/discounts'], Codes::HTTP_OK);
        }

        return $this->handleView($view);


    }
    public function postDiscountAction(){

    }

    /**
     * @param  Request $request
     * @param $cid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postDelegateAction(Request $request, $cid){
        try{
                
            $company = $this->getDoctrine()->getRepository('AppBundle:Company')->find($cid);
            $delegate = $this->createNewDelegateType($request, $company);

            $routeOptions = [
                'id'=>$delegate->getId(),
                '_format'=>$request->get('_format')
            ];

            $this->redirectView('api_get_company', $routeOptions, Codes::HTTP_CREATED);
        }
        catch(InvalidFormException $e){
            $view = $this->view(['form'=>$e->getForm()], Codes::HTTP_BADE_REQUEST);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param Company $company
     * @return CompanyDelegate|mixed
     */
    private function createNewDelegateType(Request $request, $company){
        $parameters = $request->request->all();
        $delegate = new CompanyDelegate();
        $persistedDelegate = $this->processDelegateForm($delegate, $parameters, $company, 'POST');

        return $persistedDelegate;
    }

    /**
     * @param CompanyDelegate $delegate
     * @param array $parameters
     * @param Company $company
     * @param string $method
     * @return CompanyDelegate|mixed
     */
    private function processDelegateForm(CompanyDelegate $delegate, array $parameters, $company, $method='PUT'){
        $form = $this->createForm(CompanyDelegateType::class, $delegate, ['method'=>$method, 'csrf_protection'=>false] );
        $form->submit($parameters, 'PATCH' !== $method);

        if($form->isValid()){
            /**
             * @var CompanyDelegate
             */
            $delegate = $form->getData();
            $delegate->setPassword(md5($delegate->getPassword()));
            $delegate->setCompany($company);
            $delegate->setIsDefault(0);
            $delegateRepository = $this->getDoctrine()->getRepository('AppBundle:CompanyDelegate');
            $delegateRepository->persistDelegate($delegate);

            return $delegate;
        }

        throw new InvalidFormException($this->get('translator')->trans('Invalid Submitted data'), $form);

    }

}
