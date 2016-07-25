<?php

namespace AppBundle\Rest\Customer;



use AppBundle\Entity\CustomerInvitation;
use AppBundle\Exception\InvalidFormException;
use AppBundle\Entity\Customer;
use AppBundle\Form\Invite;
use AppBundle\Rest\Codes;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CustomerController
 * @package AppBundle\Rest\Customer
 * @RouteResource("customer", pluralize=false)
 */
class CustomerController extends FOSRestController
{
    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($id){

        $customerRepository = $this->getDoctrine()->getRepository('AppBundle:Customer');

        $customer = null;

        try{
            $customer = $customerRepository->find($id);

        }
        catch (\Exception $e){
            $customer = null;
        }

        if(!$customer){
            $view = $this->view(['message'=>$this->get('translator')->trans('Record not found')], Codes::HTTP_NOT_FOUND );
        }
        else {

            $view = $this->view(['customer'=>$customer, 'base_url'=>$this->getParameter('base_url')],  Codes::HTTP_OK );
        }

        return $this->handleView($view);
    }

    /**
     *
     */
    public function cgetAction(){

        $customerRepository = $this->getDoctrine()->getRepository('AppBundle:Customer');
        $customers = null;
        $customers = $customerRepository->findAll();

        if(!$customers){
            $view = $this->view(['message'=>$this->get('translator')->trans('Records not found')], Codes::HTTP_NOT_FOUND);
        }
        else {
            $view = $this->view(['customers' => $customers], Codes::HTTP_OK);
        }

        return $this->handleView($view);


    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTransactionsAction($id){

        $transactions = $this->get('app.loyality')->getTransHistory($id);

        if($transactions){
            $view = $this->view(['transaction' => $transactions], Codes::HTTP_OK);
        }
        else{
            $view = $this->view(['message'=>$this->get('translator')->trans('Records not found')], Codes::HTTP_NOT_FOUND);
        }

        return $this->handleView($view);

    }
    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPointsAction($id){

        $customer = $this->getDoctrine()->getManager()->getRepository('AppBundle:Customer')->find($id);

        $points = $this->get('app.loyality')->getPoints($customer->getLoyalityId());

        if($points){
            $view = $this->view(['points' => $points], Codes::HTTP_OK);
        }
        else{
            $view = $this->view(['message'=>$this->get('translator')->trans('Records not found')], Codes::HTTP_NOT_FOUND);
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
     * @param Request $request
     * @param $cid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postInvitefriendAction(Request $request, $cid){
        
        $invitation = new CustomerInvitation();

        $form = $this->createForm(Invite::class, $invitation, ['method'=>'POST', 'csrf_protection'=>false] );
        //var_dump($request->request->all());die();
        $form->submit($request->request->all(),  false);
        if($form->isValid()){

            $invitation->setStatus($invitation::SEND_INVITATION);
            $customer = $this->getDoctrine()->getRepository('AppBundle:Customer')->findOneBy(
                array('id'=>$cid)
            );


            $invitation->setRefferer($customer);
            $em = $this->getDoctrine()->getManager();
            $em->persist($invitation);
            $em->flush();

            $message = \Swift_Message::newInstance()
                ->setSubject($this->get('translator')->trans('Invitation to join Mzaaya.com'))
                ->setFrom('send@example.com')
                ->setTo('welcome@gmail.com')
                ->setBody(
                    $this->renderView(
                    // app/Resources/views/Emails/registration.html.twig
                        'emails/invitation.html.twig',
                        array('id' => $invitation->getId(),
                            'email' => $invitation->getEmail(),
                            'invitor' => $customer->getName()
                        )
                    ),
                    'text/html'
                );

            //TODO: Enable the mailer service
           // $this->get('mailer')->send($message);

            return $this->handleView(['success'=>true], Codes::HTTP_OK);


        }
        else{
            $view = $this->view(['form'=>$form], Codes::HTTP_BADE_REQUEST);
            return $this->handleView($view);
        }
    }

}
