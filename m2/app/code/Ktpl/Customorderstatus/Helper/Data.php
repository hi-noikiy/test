<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\Customorderstatus\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper{

	const CONFIG_MODULE_IS_ENABLED = 'customorderstatus/general/active';
    const MULTISTORE = 'customorderstatus/general/multistore';
    const THRESOLD_AMOUNT = 'customorderstatus/general/amount';
    const CUSTOMER_GROUP = 'customorderstatus/general/specificgroups';
    const AVAILABLE_PAYMENT_METHOD = 'customorderstatus/general/availPaymentMethod';

    public function isModuleEnabled($storeId)
    {
		$isEnabled = $this->scopeConfig->getValue('ktpl_general_section/general/display_success_messages', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($isEnabled == 1)
            return true;
        

        return false;
        
    }

    public function getThresholdAmount($storeId)
    {
        $thresholdAmount = $this->scopeConfig->getValue('ktpl_general_section/general/cart_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $thresholdAmount;
    }

    public function getCustomerGroup($customerGroupId, $storeId)
    {

        $customerGroup = $this->scopeConfig->getValue('ktpl_general_section/general/customergroup', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!empty($customerGroup))
        {
        	$customerGroupArray = array_map('trim', explode(',', $customerGroup));

        	if(in_array($customerGroupId, $customerGroupArray) && $customerGroupId !== "") 
                return true;
            
        }
      	return false;
                
    }

    public function getAvilablePaymentMethod($paymentCode, $storeId)
    {
        $avilabelPaymentMethod = $this->scopeConfig->getValue('ktpl_general_section/general/paymentmethods', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!empty($avilabelPaymentMethod))
        {
			$avilabelPaymentMethodArray = explode(",", $avilabelPaymentMethod);
			if(in_array($paymentCode, $avilabelPaymentMethodArray))
				return true;			            
        }
        	return false;
    }
   
}
