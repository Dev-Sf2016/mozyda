<?php

namespace AppBundle\Rest;


use AppBundle\Entity\Company;
use AppBundle\Entity\CompanyDelegate;
use AppBundle\Entity\Customer;
use AppBundle\Form\CompanyType;
use AppBundle\Form\CustRegForm;
use FOS\RestBundle\Controller\Annotations\FileParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 7/25/16
 * Time: 12:58 AM
 */

/**
 * Class HomeController
 * @package AppBundle\Rest
 *
 * @RouteResource("Home", pluralize=false)
 */
class HomeController extends FOSRestController{


    public function getCompaniesAction(){
        $companyRepository = $this->getDoctrine()->getRepository('AppBundle:Company');
        $companies = null;
        $companies = $companyRepository->findAll();

        if(!$companies){
            $view = $this->view(['message'=>$this->get('translator')->trans('Records not found')], Codes::HTTP_NOT_FOUND);
        }
        else {
            $view = $this->view(['companies' => $companies, 'base_url'=>$this->getParameter('base_url')], Codes::HTTP_OK);
        }

        return $this->handleView($view);
    }

    /**
     * @param $cid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetCompanyDiscountAction($cid){

        $discountsRepository = $this->getDoctrine()->getRepository('AppBundle:Discount');

        $discounts = $discountsRepository->getDiscountsByCompanyIdFilterByStartAndEndDate($cid);

        if(!$discounts){
            $view = $this->view(['message'=>$this->get('translator')->trans('No Discount Available')], Codes::HTTP_NOT_FOUND);
        }
        else {
            $view = $this->view(['discounts' => $discounts, 'base_url'=>$this->getParameter('base_url')], Codes::HTTP_OK);
        }

        return $this->handleView($view);


    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */
    public function postRegistercompanyAction(Request $request){

        $parameters = $request->request->all();

        $content = base64_decode($parameters['logo']);
        $mimeType = $parameters['mimeType'];
        unset($parameters['mimeType']);
        $tmpFile = tmpfile();
        fwrite($tmpFile, $content);
        stream_set_blocking($tmpFile, false);
        $metadata = stream_get_meta_data($tmpFile);
        $path = $metadata['uri'];

        // the UploadedFile of the user image
        // referencing the temp file (used for validation only)
        $uploadedFile = new UploadedFile($path, $path, $mimeType, null, null, true);

        $delegate = new CompanyDelegate();
        $company = new Company();
        $parameters['logo'] = $uploadedFile;
        $company->addCompanyDelegate($delegate);
        $form = $this->createForm(CompanyType::class, $company,  ['method'=>'POST', 'csrf_protection'=>false]);
        $form->submit($parameters, false);
        if($form->isValid()){

            $fileName = sprintf('%s-%s.%s', uniqid(), time(), $uploadedFile->guessExtension());
            // Move the file to the directory where brochures are stored

            $uploadedFile->move(

                $this->container->getParameter("app.company.logo_path"),
                $fileName
            );
            fclose($tmpFile);
            $company->setLogo($fileName);
            $company->setIsActive(0);
            $em = $this->getDoctrine()->getManager();

            $em->persist($company);

            $delegate->setPassword(md5($delegate->getPassword()));
            $delegate->setIsDefault('1');

            $delegate->setCompany($company);
            $em->persist($delegate);
            $em->flush();

            $view = $this->view(['success'=>true], Codes::HTTP_OK);


        }
        else{
            $view = $this->view(['form'=>$form], Codes::HTTP_BADE_REQUEST);
        }

        return $this->handleView($view);
        
    }

    public function postRegistercustomerAction(Request $request){
        $parameters = $request->request->all();
        $customer = new Customer();

        $form = $this->createForm(CustRegForm::class, $customer, ['method'=>'POST', 'csrf_protection'=>false] );
        $form->submit($parameters, false);

        if($form->isValid()){
            //$customer = $form->getData();
            $customer->setActivationCode(rand(111111, 999999));
            $customer->setIsActive(0);
            $customer->setPassword(md5($customer->getPassword()));
            $customerRepository = $this->getDoctrine()->getRepository('AppBundle:Customer');
            $customerRepository->persistCustomer($customer);

            $message = \Swift_Message::newInstance()
                ->setSubject($this->get('translator')->trans('Account Activation'))
                ->setFrom($this->getParameter("email_from"))
                ->setTo($customer->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/mobile-registration.html.twig',
                        array(
                            'id' => $customer->getId(),
                            'code' => $customer->getActivationCode(),
                            'name' => $customer->getName()
                        )
                    ),
                    'text/html'
                );

//                echo $message;
            $this->get('mailer')->send($message);
            $view = $this->view([
                'message'=>$this->get('translator')->trans('A code is send to your provided email. Enter the code you received to continue with the signup process'),
                'email'=>$customer->getEmail(), 'id'=>$customer->getId(),
                "hash"=>md5($customer->getActivationCode() . $customer->getPassword() )], Codes::HTTP_OK);
        }
        else{
            $view = $this->view(['form'=>$form], Codes::HTTP_BADE_REQUEST);
        }

        return $this->handleView($view);
    }

    public function postActivatecustomerAction(Request $request){

        $id = $request->get('id', '');
        $email = $request->get('email', '');
        $hash = $request->get('hash', '');
        $code = $request->get('code', '');

        if($id == "" || $email=="" || $hash == "" || $code == ""){

            $view = $this->view(['message'=>$this->get('translator')->trans('Invalid Code')], Codes::HTTP_BADE_REQUEST);
        }
        else{

            $em = $this->getDoctrine()->getManager();
            $customer = $em->getRepository('AppBundle:Customer')->find($id);

            if($customer && $customer->getEmail() == $email && $customer->getActivationCode() == $code && $hash == md5($customer->getActivationCode() . $customer->getPassword())){
                $customer->setIsActive(1);
                $em->persist($customer);
                $em->flush();

                $view = $this->view(['message'=>$this->get('translator')->trans('Your account is created successfully.  You can now Login to the system')], Codes::HTTP_OK);

            }
            else{
                $view = $this->view([ 'message'=>$this->get('translator')->trans('Invalid Code')], Codes::HTTP_BADE_REQUEST);
            }
        }

        return $this->handleView($view);
    }
}