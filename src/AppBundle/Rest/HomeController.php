<?php

namespace AppBundle\Rest;


use AppBundle\Entity\Company;
use AppBundle\Entity\CompanyDelegate;
use AppBundle\Form\CompanyType;
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

    public function getCompaniesDiscountAction($cid){

        $discountsRepository = $this->getDoctrine()->getRepository('AppBundle:Discount');

        $discounts = $discountsRepository->findAll();

        if(!$discounts){
            $view = $this->view(['message'=>$this->get('translator')->trans('Records not found')], Codes::HTTP_NOT_FOUND);
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
        $tmpFile = tmpfile();
        fwrite($tmpFile, $content);
        stream_set_blocking($tmpFile, false);
        $metadata = stream_get_meta_data($tmpFile);
        $path = $metadata['uri'];


        // the UploadedFile of the user image
        // referencing the temp file (used for validation only)
        $uploadedFile = new UploadedFile($path, $path, 'jpg', null, null, true);


        $delegate = new CompanyDelegate();
        $company = new Company();

        $parameters['logo'] = $uploadedFile;
        
        $company->addCompanyDelegate($delegate);

        $form = $this->createForm(CompanyType::class, $company,  ['method'=>'POST', 'csrf_protection'=>false]);

        $form->submit($parameters, false);

        if($form->isValid()){

            $fileName = sprintf('%s-%s.%s', uniqid(), time(), 'jpg');

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
            $view = $this->view(['form'=>$form], Codes::HTTP_FORBIDDEN);
        }

        return $this->handleView($view);
        
    }

    public function postRegistercustomerAction(Request $request){

    }
}