<?php

namespace AppBundle\Rest\Controller;


use AppBundle\Entity\Company;
use AppBundle\Exception\InvalidFormException;
use AppBundle\Form\CompanyType;
use AppBundle\Rest\Codes;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CompanyController
 * @package AppBundle\Rest\Controller
 * @RouteResource("Company", pluralize=false)
 */
class CompanyController extends FOSRestController
{
    /**
     *
     * @param $id
     * @return mixed
     */
    public function getAction($id)
    {
        $companyRepository = $this->getDoctrine()->getRepository('AppBundle:Company');

        $company = null;
        try{
            $company = $companyRepository->find($id);
        }
        catch (\Exception $e){
            $company = null;
        }

        if(!$company){
            $view = $this->view(array('success'=>false, 'message'=>$this->get('translator')->trans('Record not found')));
        }
        else {
            $view = $this->view(array('success' => true, 'company' => $company), 200);
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
            $view = $this->view(array('success'=>false, 'message'=>$this->get('translator')->trans('Records not found')));
        }
        else {
            $view = $this->view(array('success' => true, 'companies' => $companies), 200);
        }

        return $this->handleView($view);


    }

    public function newtAction(Request $request){

    }

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
            $view = $this->view(array('success'=>false, 'message'=>$this->get('translator')->trans('Invalid form')));
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
        $form = $this->createForm(CompanyType::class, $company, ['method'=>$method] );
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
}
