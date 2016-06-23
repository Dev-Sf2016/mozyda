<?php
namespace AppBundle\Controller\rest;
use Symfony\Component\DependencyInjection\Tests\Compiler\C;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class DefaultController extends Controller
{
    /**
     * @Route("/restusers", name="rest_users")
     * @return JsonResponse
     */
    public function getUsersAction()
    {

//        echo $this->json(array('username' => 'jane.doe'));
//       die('--');
//        return $this->json(array('username' => 'jane.doe'));
        return new Response(json_encode(array('username' => 'jane.doe')));
//        return new Response($this->json(array('username' => 'jane.doe'))->getContent());
    }

}
?>