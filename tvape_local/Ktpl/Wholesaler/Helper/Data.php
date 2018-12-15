<?php

class Ktpl_Wholesaler_Helper_Data extends Mage_Core_Helper_Abstract {
   public $_wholesalerlabel = '';
    /**
     * 
     * @param type $baseGrandTotal
     * @return type
     */
    public function calculateCustomDiscount($baseGrandTotal,$customergroup=null,$storeid=null) {
        $per = 0;
        if($this->Enabletierdiscount($storeid)){
            if(in_array($customergroup, $this->enablecustomergroup($storeid))){
                $discounts = (unserialize(Mage::getStoreConfig('wholesaler/general/wholesaler_discount',$storeid)));
                foreach ($discounts as $discount) {
                    $temp[$discount['total']] = array($discount['discount'],$discount['name']);
                }
                krsort($temp);
                foreach ($temp as $key => $t) {
                    if ($key <= $baseGrandTotal) {
                        $per = $t[0];
                        $this->_wholesalerlabel = $t[1]; 
                        break;
                    }
                }
            }
        }
        return $per;
    }
    
    /**
     * 
     * @param type $order
     * @return type
     */
    public function calculateTax($order) {
        $discount_amount = 0;
        $storeid = $order->getStoreId();
        if(in_array($order->getCustomerGroupId(), $this->enablecustomergroup($storeid))){
            $discount = $this->calculateCustomDiscount($order->getSubtotal(),$order->getCustomerGroupId(),$storeid);
            $discount_amount = -(($order->getSubtotal()+$order->getShippingAmount()) * $discount / 100);
        }
        return $discount_amount;
    }
    
    public function getlabel(){
        return $this->_wholesalerlabel;
    }
    
    public function Wholesalercategoryurl(){
        $categoryId = Mage::getStoreConfig('wholesaler/general/categoryid');
        if($categoryId==''){
            $categoryId=71;
        }
        return Mage::getModel('catalog/category')->load($categoryId)->getUrl();
    }
    
    public function Enabletierdiscount($storeid = null){
        $configValue = Mage::getStoreConfig('wholesaler/general/active',$storeid);
        return $configValue ? $configValue : false;
    }
    
    public function enablecustomergroup($storeid = null){
        $cg = Mage::getStoreConfig('wholesaler/general/customergroup',$storeid);
        $customergroup = explode(',', $cg);
        return $customergroup;
    }

}
