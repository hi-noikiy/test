<?php
class EM_Customercomment2_Model_Sales_Order extends Mage_Sales_Model_Order
{
   public function hasCustomFields(){
        $var = $this->getSsn();
        if($var && !empty($var)){
            return true;
        }else{
            return false;
        }
    }
    public function getFieldHtml(){
        $var = $this->getSsn();
        $html = $var.'<br/>';
        return $html;
    }
}
		