<?php

class Thinkhigh_VPCpaymentgateway_Block_Info_Vpcsave extends Mage_Payment_Block_Info
{
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $info = $this->getInfo();
        if($info->getVpcCard()){
            $transport = new Varien_Object(array(Mage::helper('payment')->__('Credit Card Type') => $info->getVpcCard()));
        }
        $transport = parent::_prepareSpecificInformation($transport);
        /*if (!$this->getIsSecureMode()) {
            $transport->addData(array(
                Mage::helper('payment')->__('Expiration Date') => $this->_formatCardDate(
                    $info->getCcExpYear(), $this->getCcExpMonth()
                ),
                Mage::helper('payment')->__('Credit Card Number') => $info->getCcNumber(),
            ));
        }                                 */
        return $transport;
    }
    
}
