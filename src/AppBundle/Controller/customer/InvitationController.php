<?php
namespace AppBundle\Controller\customer;

use AppBundle\Entity\CustomerInvitation;
use AppBundle\Form\Invite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class InvitationController extends Controller
{
/**
 * @Route("customer/invite", name="customer_invite")
 */
    public function customerInvite(Request $request){
        $customerInvitation = new CustomerInvitation();
        $form = $this->createForm(Invite::class, $customerInvitation);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $customerInvitation->setStatus($customerInvitation::SEND_INVITATION);
            $customer = $this->getDoctrine()->getRepository('AppBundle:Customer')->findOneBy(
                array('id'=>$this->getUser()->getId())
            );


            $customerInvitation->setRefferer($customer);
            $em = $this->getDoctrine()->getManager();
            $em->persist($customerInvitation);
            $em->flush();
            // send invitation email
            $message = \Swift_Message::newInstance()
                ->setSubject($this->get('translator')->trans('Invitation to join Mzaaya.com'))
                ->setFrom($this->getParameter("email_from"))
                ->setTo($customerInvitation->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/invitation.html.twig',
                        array('id' => $customerInvitation->getId(),
                                'email' => $customerInvitation->getEmail(),
                                'invitor' => $customer->getName()
                        )
                    ),
                    'text/html'
                );

//                echo $message;
        $this->get('mailer')->send($message);
            $this->addFlash('success', $this->get('translator')->trans('Invitation has been sent sucessfully'));

        }

        return $this->render(':customer:invite.html.twig',
            [
                'form' => $form->createView()
            ]);
    }
}