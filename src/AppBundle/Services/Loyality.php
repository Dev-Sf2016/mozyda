<?php
namespace AppBundle\Services;

class Loyality{

    public function getPoints($id){
        if($id>100 && $id<200) {
            return array('id'=>$id, 'points' => 90);
        }else{
            return ("invlaid card");
        }
    }

    public function checkCardStatus($id){
        if($id>100 && $id<200) {
            return true;
        }else{
            return false;
        }
    }
    public function getCustInfo($id){
        $info = array('cust_name'=>'Test Cust Name',
                      'email' => 'test@test.com',
                      'last_card_usage' => '05/05/2016',
                      'card_avtivation_date' => '05/04/2016',
                      'mobile_no' => '00966569888888',
                      'card_status' => 'Active',
                      'customer_nationality' => 'eg',
                      'maritial_status' => 'married',
                      'current_balance' => '80.00SR'

        );
        if($id>100 && $id<200) {
            return array('cust_info' => $info);
        }else{
            return array('status' => false);
        }
    }
    public function getCustTransStats($id){
        $info = array(
          'jan' => array('usage' => '10', 'points' =>'45'),
          'feb' => array('usage' => '3', 'points' =>'8'),
          'mar' => array('usage' => '10', 'points' =>'4')
        );

        if($id>100 && $id<200) {
            return array('cust_info' => $info);
        }else{
            return array('status' => false);
        }
    }
}
?>