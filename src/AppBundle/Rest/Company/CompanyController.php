<?php

namespace AppBundle\Rest\Company;


use AppBundle\Entity\Company;
use AppBundle\Entity\CompanyDelegate;
use AppBundle\Entity\Discount;
use AppBundle\Exception\InvalidFormException;
use AppBundle\Form\CompanyDelegateType;
use AppBundle\Form\CompanyType;
use AppBundle\Form\DiscountType;
use AppBundle\Rest\Codes;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

    private function isUserValid($companyId){
        $userid = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:CompanyDelegate')->find($userid);
        if( $user->getCompany()->getId()  == $companyId){
            return true;
        }

        return false;

    }

    public function getLoginAction(){
        if(!$this->isAllowed()){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $userInfo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CompanyDelegate')->find($user->getId());

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

            $view = $this->view(['delegate'=>$delegate, 'base_url'=>$this->getParameter('base_url')],  Codes::HTTP_OK );
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
    public function putAction(Request $request, $id){

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
        if(!$this->isAllowed() || !$this->isUserValid($cid)){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }

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
        if(!$this->isAllowed() || !$this->isUserValid($cid)){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }

        $discountsRepository = $this->getDoctrine()->getRepository('AppBundle:Discount');
        $discounts = $discountsRepository->findBy(array('company'=>$cid));
        if(!$discounts){
            $view = $this->view(['message'=>$this->get('translator')->trans('Records not found')], Codes::HTTP_NOT_FOUND);
        }
        else {
            $view = $this->view(['discounts' => $discounts, 'base_url'=>'http://mozyda.dev/files/company/discounts'], Codes::HTTP_OK);
        }
        return $this->handleView($view);

    }

    /**
     * @param Request $request
     * @param $cid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postDiscountAction(Request $request, $cid){
        if(!$this->isAllowed() || !$this->isUserValid($cid)){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }

        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:CompanyDelegate')->find($userId);
        $view = null;
        if($user->getCompany()->getId() == $cid){

            $parameters = $request->request->all();

            $parameters['startDate'] = date('Y-m-d', strtotime($parameters['startDate']));
            $parameters['endDate'] = date('Y-m-d', strtotime($parameters['endDate']));

            $content = base64_decode($parameters['promotion']);
            $mimeType = $parameters['mimeType'];
            unset($parameters['mimeType']);
            $tmpFile = tmpfile();
            fwrite($tmpFile, $content);
            stream_set_blocking($tmpFile, false);
            $metadata = stream_get_meta_data($tmpFile);
            $path = $metadata['uri'];
            $uploadedFile = new UploadedFile($path, $path, $mimeType, null, null, true);
            $parameters['promotion'] = $uploadedFile;
            $discount = new Discount();
            $discount->setCompany($user->getCompany());

            $form = $this->createForm(DiscountType::class, $discount, ['method'=>'POST', 'csrf_protection'=>false]);

            $form->submit($parameters, false);

            if($form->isValid()){
                // save discount
                $fileName = sprintf('%s-%s.%s', uniqid(), time(), $uploadedFile->guessExtension());
                //check director exist or not
                $upload_dir = $this->getParameter("app.company.discount_path") . "/" . $user->getCompany()->getId();
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir);
                }
                $uploadedFile->move($upload_dir, $fileName);
                $discount->setPromotion($fileName);
                $em = $this->getDoctrine()->getManager();

                $em->persist($discount);
                $em->flush();

                $view = $this->view(['message'=>$this->get('translator')->trans('discount is uploaded successfully')], Codes::HTTP_OK);
            }
            else{
                $view = $this->view(['form'=>$form], Codes::HTTP_BADE_REQUEST);
            }

        }
        else{
            $view = $this->view([], Codes::HTTP_FORBIDDEN);
        }

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $cid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postPdiscountAction(Request $request, $cid){

        if(!$this->isAllowed() || !$this->isUserValid($cid)){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }

        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:CompanyDelegate')->find($userId);

        $parameters = $request->request->all();
        $discountId = $parameters['id'];
        unset($parameters['id']);
        $discount = $this->getDoctrine()->getRepository('AppBundle:Discount')->find($discountId);
        $promotion = $discount->getPromotion();
        $view = null;
        if($user->getCompany()->getId() == $cid && $discount->getCompany()->getId() == $cid){

            $parameters['startDate'] = date('Y-m-d', strtotime($parameters['startDate']));
            $parameters['endDate'] = date('Y-m-d', strtotime($parameters['endDate']));

            if(isset($parameters['promotion'])){
                $content = base64_decode($parameters['promotion']);
                $mimeType = $parameters['mimeType'];
                unset($parameters['mimeType']);
                $tmpFile = tmpfile();
                fwrite($tmpFile, $content);
                stream_set_blocking($tmpFile, false);
                $metadata = stream_get_meta_data($tmpFile);
                $path = $metadata['uri'];
                $uploadedFile = new UploadedFile($path, $path, $mimeType, null, null, true);
                $parameters['promotion'] = $uploadedFile;
            }

            $form = $this->createForm(DiscountType::class, $discount, ['method'=>'PATCH', 'csrf_protection'=>false]);

            $form->submit($parameters, true);

            if($form->isValid()){
                // save discount
                if(isset($parameters['promotion'])) {
                    $fileName = sprintf('%s-%s.%s', uniqid(), time(), $uploadedFile->guessExtension());
                    //check director exist or not
                    $upload_dir = $this->getParameter("app.company.discount_path") . "/" . $user->getCompany()->getId();
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir);
                    }
                    $uploadedFile->move($upload_dir, $fileName);
                    $discount->setPromotion($fileName);
                }
                else{
                    $discount->setPromotion($promotion);
                }
                $em = $this->getDoctrine()->getManager();

                $em->persist($discount);
                $em->flush();

                $view = $this->view(['message'=>$this->get('translator')->trans('discount is updated successfully')], Codes::HTTP_OK);
            }
            else{
                $view = $this->view(['form'=>$form], Codes::HTTP_BADE_REQUEST);
            }

        }
        else{
            $view = $this->view([], Codes::HTTP_FORBIDDEN);
        }

        return $this->handleView($view);
    }
    /**
     * @param  Request $request
     * @param $cid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postDelegateAction(Request $request, $cid){

        if(!$this->isAllowed() || !$this->isUserValid($cid)){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }

        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:CompanyDelegate')->find($userId);
        $view = null;
        if($user->getCompany()->getId() == $cid){
            $delegate = new CompanyDelegate();
            $parameters = $request->request->all();

            $form = $this->createForm(CompanyDelegateType::class, $delegate, ['method'=>'POST', 'csrf_protection'=>false]);

            $form->submit($parameters, false);

            if($form->isValid()){
                // save delegate
                $password = $delegate->getPassword();
                $delegate->setPassword(md5($delegate->getPassword()));
                $delegate->setCompany($user->getCompany());
                $delegate->setIsDefault(0);
                $delegateRepository = $this->getDoctrine()->getRepository('AppBundle:CompanyDelegate');
                $delegateRepository->persistDelegate($delegate);

                $message = \Swift_Message::newInstance()
                    ->setSubject($this->get('translator')->trans('Delegation Account created'))
                    ->setFrom($this->getParameter("email_from"))
                    ->setTo($delegate->getEmail())
                    ->setBody(
                        $this->renderView(
                            'emails/delegate-new-account-added.html.twig',
                            array(

                                'email' => $delegate->getEmail(),
                                'name' => $delegate->getName(),
                                'password' => $password,
                                'company_name' => $user->getCompany()->getName(),
                                'url' => $this->generateUrl('account_login', array(), UrlGeneratorInterface::ABSOLUTE_URL)
                            )
                        )
                    );


//                echo $message;
                $this->get('mailer')->send($message);

                $view = $this->view(['message'=>$this->get('translator')->trans('Delegate is added successfully')], Codes::HTTP_OK);
            }
            else{
                $view = $this->view(['form'=>$form], Codes::HTTP_BADE_REQUEST);
            }

        }
        else{
            $view = $this->view([], Codes::HTTP_FORBIDDEN);
        }

        return $this->handleView($view);

    }

    /**
     * @param  Request $request
     * @param $cid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postPdelegateAction(Request $request, $cid){
        if(!$this->isAllowed() || !$this->isUserValid($cid)){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }

        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:CompanyDelegate')->find($userId);
        $view = null;
        $parameters = $request->request->all();
        $delegate = $this->getDoctrine()->getRepository("AppBundle:CompanyDelegate")->find($parameters['id']);
        unset($parameters['id']);
        if($user->getCompany()->getId() == $cid && $delegate){


            $form = $this->createForm(CompanyDelegateType::class, $delegate, ['method'=>'PATCH', 'csrf_protection'=>false]);

            $form->submit($parameters, false);

            if($form->isValid()){
                $delegateRepository = $this->getDoctrine()->getRepository('AppBundle:CompanyDelegate');
                $delegateRepository->persistDelegate($delegate);

                $view = $this->view(['message'=>$this->get('translator')->trans('Delegate is updated successfully')], Codes::HTTP_OK);
            }
            else{
                $view = $this->view(['form'=>$form], Codes::HTTP_BADE_REQUEST);
            }

        }
        else{
            $view = $this->view([], Codes::HTTP_FORBIDDEN);
        }

        return $this->handleView($view);

    }

    /**
     * @param $cid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetDelegateAction($cid){
        if(!$this->isAllowed() || !$this->isUserValid($cid)){
            return $this->handleView($this->view([], Codes::HTTP_UN_AUTHORIZED ));
        }

        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:CompanyDelegate')->find($userId);
        $view = null;
        if($user->getCompany()->getId() == $cid){
            
            $delegateRepository = $this->getDoctrine()->getRepository('AppBundle:CompanyDelegate');
            $delegates = $delegateRepository->getDelegatesByCompanyExcludeDefaultDelegate($user->getCompany());
            if(!empty($delegates))
                $view = $this->view(['delegates'=>$delegates], Codes::HTTP_OK);
            else{
                $view = $this->view(['message'=>$this->get('translator')->trans("No Delegate Found")], Codes::HTTP_NOT_FOUND);
            }
            
        }
        else{
            $view = $this->view([], Codes::HTTP_FORBIDDEN);
        }

        return $this->handleView($view);
    }

}
