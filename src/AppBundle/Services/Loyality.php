<?php
namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\TranslatorInterface;

class Loyality
{
    private $em;
    private $translator;

    public function checkLoyalityCard($id)
    {
        // check if loyality card is alreayd registered to a user in our website db
//        $em = $this->getDoctrine()->getManager();
        $user = $this->em->getRepository('AppBundle:Customer')->findOneBy(
            array('loyalityId' => $id)
        );
        if (!$user) {
            // this card is not registered
            // check from POS service
//            $loyality = $this->get('app.loyality');
            $cardStatus = $this->checkCardStatus($id);
            if ($cardStatus) {
                return array('status' => true, 'msg' => $this->translator->trans('OK'));
            } else {
                return array('status' => false, 'msg' => $this->translator->trans("Invalid Card Number"));
            }

        } else {
            return array('status' => false, 'msg' => $this->translator->trans("Card already registered"));
        }
    }

    public function getPoints($id)
    {
        if ($id > 100 && $id < 200) {
            return array('id' => $id, 'points' => 90);
        } else {
            return ("invlaid card");
        }
    }

    public function checkCardStatus($id)
    {
        if ($id > 100 && $id < 200) {
            return true;
        } else {
            return false;
        }
    }

    public function getCustInfo($id)
    {
        $info = array('cust_name' => 'Test Cust Name',
            'email' => 'test@test.com',
            'last_card_usage' => '05/05/2016',
            'card_avtivation_date' => '05/04/2016',
            'mobile_no' => '00966569888888',
            'card_status' => 'Active',
            'customer_nationality' => 'eg',
            'maritial_status' => 'married',
            'current_balance' => '80.00SR'

        );
        if ($id > 100 && $id < 200) {
            return array('cust_info' => $info);
        } else {
            return array('status' => false);
        }
    }

    public function getCustTransStats($id)
    {
        $info = array(
            'jan' => array('usage' => '10', 'points' => '45'),
            'feb' => array('usage' => '3', 'points' => '8'),
            'mar' => array('usage' => '10', 'points' => '4')
        );

        if ($id > 100 && $id < 200) {
            return array('cust_info' => $info);
        } else {
            return array('status' => false);
        }
    }

    public function getTransHistory($id)
    {
        $info = array(
            array('Store' => 'Extra Store', 'InvoiceAmount' => '234.33 SAR', 'PointsGained' => '20', 'Date' => '2016-05-22: 08:22:10'),
            array('Store' => 'Extra Store', 'InvoiceAmount' => '234.33 SAR', 'PointsGained' => '20', 'Date' => '2016-05-22: 08:22:10'),
            array('Store' => 'Extra Store', 'InvoiceAmount' => '234.33 SAR', 'PointsGained' => '20', 'Date' => '2016-05-22: 08:22:10'),
            array('Store' => 'Extra Store', 'InvoiceAmount' => '234.33 SAR', 'PointsGained' => '20', 'Date' => '2016-05-22: 08:22:10'),
            array('Store' => 'Extra Store', 'InvoiceAmount' => '234.33 SAR', 'PointsGained' => '20', 'Date' => '2016-05-22: 08:22:10'),
            array('Store' => 'Extra Store', 'InvoiceAmount' => '234.33 SAR', 'PointsGained' => '20', 'Date' => '2016-05-22: 08:22:10'),
            array('Store' => 'Extra Store', 'InvoiceAmount' => '234.33 SAR', 'PointsGained' => '20', 'Date' => '2016-05-22: 08:22:10'),
            array('Store' => 'Extra Store', 'InvoiceAmount' => '234.33 SAR', 'PointsGained' => '20', 'Date' => '2016-05-22: 08:22:10'),
            array('Store' => 'Extra Store', 'InvoiceAmount' => '234.33 SAR', 'PointsGained' => '20', 'Date' => '2016-05-22: 08:22:10'),
            array('Store' => 'Extra Store', 'InvoiceAmount' => '234.33 SAR', 'PointsGained' => '20', 'Date' => '2016-05-22: 08:22:10')
        );

        return $info;
    }

    public function __construct(EntityManager $entityManager, TranslatorInterface $translatorInterface)
    {
        $this->em = $entityManager;
        $this->translator = $translatorInterface;
    }
}

?>