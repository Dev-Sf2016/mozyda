<?php

namespace AppBundle\Rest\Customer;



use AppBundle\Entity\CustomerInvitation;
use AppBundle\Exception\InvalidFormException;
use AppBundle\Entity\Customer;
use AppBundle\Form\CustRegForm;
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
    private function isAllowed()
    {
        //$token = $this->get('security.token_storage')->getToken();
        if($this->get('security.token_storage')->getToken()->area == 'customer'){
            return true;
        }

        return false;
    }
    public function getLoginAction(){
        if(!$this->isAllowed()){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $userInfo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Customer')->find($user->getId());

        $userInfo->setPassword('');
        $view = $this->view(['user'=>$userInfo, 'base_url'=>$this->getParameter('base_url')],  Codes::HTTP_OK );

        return $this->handleView($view);
    }


    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($id){
        if(!$this->isAllowed()){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTransactionsAction(){
        if(!$this->isAllowed()){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $customer = $this->getDoctrine()->getManager()->getRepository('AppBundle:Customer')->find($user->getId());
        $transactions = $this->get('app.loyality')->getTransHistory($customer->getLoyalityId());

        if($transactions){
            $view = $this->view(['transaction' => $transactions], Codes::HTTP_OK);
        }
        else{
            $view = $this->view(['message'=>$this->get('translator')->trans('Records not found')], Codes::HTTP_NOT_FOUND);
        }

        return $this->handleView($view);

    }
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPointsAction(){
        if(!$this->isAllowed()){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $customer = $this->getDoctrine()->getManager()->getRepository('AppBundle:Customer')->find($user->getId());

        $points = $this->get('app.loyality')->getPoints($customer->getLoyalityId());

        if($points){
            $view = $this->view(['points' => $points], Codes::HTTP_OK);
        }
        else{
            $view = $this->view(['message'=>$this->get('translator')->trans('Records not found')], Codes::HTTP_NOT_FOUND);
        }

        return $this->handleView($view);

    }


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction(Request $request){
        if(!$this->isAllowed()){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $customer = $em->getRepository('AppBundle:Customer')->find($user->getId());
        $parameters = $request->request->all();
        $parameters['email'] = $customer->getEmail();
        if(isset($parameters['password']['first']) && $parameters['password']['first']!=''){
            $md5Password = md5($parameters['password']['first']);
            $parameters['password']['first'] = $md5Password;
            //$parameters['password']['second'] = $md5Password;
        }
        else{
            $parameters['password']['first'] = $customer->getPassword();
            $parameters['password']['second'] = $customer->getPassword();
        }

        $form = $this->createForm(CustRegForm::class, $customer, ['method'=>'PATCH', 'csrf_protection'=>false]);
        $form->submit($parameters, true);

        if($form->isValid()){
            $em->persist($customer);
            $em->flush();
            $customer->setPassword('');
            $tran = $this->get('translator');
            $view = $this->view(['modal'=>[
                'user'=>$customer, 'base_url'=>$this->getParameter('base_url')],
                    'message'=>$tran->trans('Customer Information is updated successfully')], Codes::HTTP_OK);
        }
        else{
            $view = $this->view(['form'=>$form], Codes::HTTP_BADE_REQUEST);
        }


        return $this->handleView($view);

    }


    /**
     * @param Request $request
     * @param $cid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postInvitefriendAction(Request $request, $cid){
        if(!$this->isAllowed()){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $invitation = new CustomerInvitation();

        $form = $this->createForm(Invite::class, $invitation, ['method'=>'POST', 'csrf_protection'=>false] );
        //var_dump($request->request->all());die();
        $form->submit($request->request->all(),  false);
        if($form->isValid()){

            $invitation->setStatus($invitation::SEND_INVITATION);
            $customer = $this->getDoctrine()->getRepository('AppBundle:Customer')->find($user->getId());


            $invitation->setRefferer($customer);
            $em = $this->getDoctrine()->getManager();
            $em->persist($invitation);
            $em->flush();

            $message = \Swift_Message::newInstance()
                ->setSubject($this->get('translator')->trans('Invitation to join Mzaaya.com'))
                ->setFrom($this->getParameter("email_from"))
                ->setTo($invitation->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/invitation.html.twig',
                        array('id' => $invitation->getId(),
                            'email' => $invitation->getEmail(),
                            'invitor' => $customer->getName()
                        )
                    ),
                    'text/html'
                );

            $this->get('mailer')->send($message);

            $view = $this->view(['success'=>true], Codes::HTTP_OK);
            return $this->handleView($view);


        }
        else{
            $view = $this->view(['form'=>$form], Codes::HTTP_BADE_REQUEST);
            return $this->handleView($view);
        }
    }

}
