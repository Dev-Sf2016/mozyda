<?php

namespace AppBundle\Rest\Company;


use AppBundle\Entity\CompanyDelegate;
use AppBundle\Exception\InvalidFormException;
use AppBundle\Rest\Codes;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class DelegateController
 * @package AppBundle\Controller\Rest\Company
 * @RouteResource("delegate", pluralize=false)
 */
class DelegateController extends FOSRestController
{
    /**
     * @param $cid
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCompanyAction($cid, $id){

        $delegateRepository = $this->getDoctrine()->getRepository('AppBundle:CompanyDelegate');

        $delegate = null;
        try{
            $delegate = $delegateRepository->find($id);
        }
        catch (\Exception $e){
            $company = null;
        }

        if(!$delegate){
            $view = $this->view(['message'=>$this->get('translator')->trans('Record not found')], Codes::HTTP_NOT_FOUND );
        }
        else {
            $view = $this->view(['company'=> $delegate],  Codes::HTTP_OK );
        }

        return $this->handleView($view);
    }

    /**
     *
     */
    public function cgetAction(){
        $delegateRepository = $this->getDoctrine()->getRepository('AppBundle:CompanyDelegate');
        $delegates = null;
        $delegates = $delegateRepository->findAll();

        if(!$delegates){
            $view = $this->view(['message'=>$this->get('translator')->trans('Records not found')], Codes::HTTP_NOT_FOUND);
        }
        else {
            $view = $this->view(['delegates' => $delegates], Codes::HTTP_OK);
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
        //$companyRepository = $this->getDoctrine()->getRepository('AppBundle:Delegate');

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
     * @return Delegate|mixed
     */
    private function createNewType(Request $request){
        $parameters = $request->request->all();
        $company = new Delegate();

        $companyDelegate = new DelegateDelegate();
        $company->addDelegateDelegate($companyDelegate);

        $persistedDelegate = $this->processForm($company, $parameters, 'POST');

        return $persistedDelegate;


    }

    /**
     * @param Delegate $company
     * @param array $parameters
     * @param string $method
     * @return Delegate|mixed
     */
    private function processForm(Delegate $company, array $parameters, $method='PUT'){
        $form = $this->createForm(DelegateType::class, $company, ['method'=>$method, 'csrf_protection'=>false] );
        $form->submit($parameters, 'PATCH' !== $method);

        if($form->isValid()){
            $company = $form->getData();

            $companyRepository = $this->getDoctrine()->getRepository('AppBundle:Delegate');
            $companyRepository->persistDelegate($company);

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
            $company = $this->getDoctrine()->getRepository("AppBundle:Delegate")->find($id);

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
