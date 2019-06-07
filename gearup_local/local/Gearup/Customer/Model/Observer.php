<?php

class Gearup_Customer_Model_Observer {

    public function updateCustomerName($observer) {
        $customer = $observer->getCustomer();
        $name = $customer->getName();
        $this->_common($name, $customer);
    }

    public function updateCustomerAddressName($observer) {
        $customer = $observer->getCustomerAddress();
        $name = $customer->getName();
        $this->_common($name, $customer);
    }

    private function _common($name = null, $customer) {
        $request = Mage::app()->getRequest();
        $module_controller_action = $request->getActionName();
        if ($module_controller_action != 'defualtAddress') {
            if ($customer->getName()){
                $lname = '';
                $firstName = explode(' ', $customer->getName());
                $customer->setFirstname($firstName[0]);
                foreach($firstName as $k=> $fir){
                    if($k==0){continue;}
                    $lname .= $fir.' ';
                }
                $customer->setLastname(rtrim($lname));
                $customer->setName($customer->getName());
            }
        }
    }

}
