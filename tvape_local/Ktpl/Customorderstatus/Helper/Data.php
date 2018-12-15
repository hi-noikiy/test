<?php
class Ktpl_Customorderstatus_Helper_Data extends Mage_Core_Helper_Abstract
{
	const CONFIG_MODULE_IS_ENABLED = 'customorderstatus/general/active';
    const MULTISTORE = 'customorderstatus/general/multistore';
    const THRESOLD_AMOUNT = 'customorderstatus/general/amount';
    const CUSTOMER_GROUP = 'customorderstatus/general/specificgroups';
    const AVAILABLE_PAYMENT_METHOD = 'customorderstatus/general/availPaymentMethod';

    public function isModuleEnabled($storeId)
    {
        $isEnabled = Mage::getStoreConfig(self::CONFIG_MODULE_IS_ENABLED, $storeId);

        if($isEnabled == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function getThresholdAmount($storeId)
    {
        $thresholdAmount = Mage::getStoreConfig(self::THRESOLD_AMOUNT, $storeId);
        return $thresholdAmount;
    }

    public function getCustomerGroup($customerGroupId, $storeId)
    {
        $customerGroup = Mage::getStoreConfig(self::CUSTOMER_GROUP, $storeId);
        if(!empty($customerGroup))
        {
        	$customerGroupArray = array_map('trim', explode(',', $customerGroup));

        	if(in_array($customerGroupId, $customerGroupArray) && $customerGroupId !== "") {
                return true;
            } else {
               	return false;
            }
        }else{
        	return false;
        }        
    }

    public function getAvilablePaymentMethod($paymentCode, $storeId)
    {
        $avilabelPaymentMethod = Mage::getStoreConfig(self::AVAILABLE_PAYMENT_METHOD, $storeId);
        
        if(!empty($avilabelPaymentMethod))
        {
			$avilabelPaymentMethodArray = explode(",", $avilabelPaymentMethod);
			if(in_array($paymentCode, $avilabelPaymentMethodArray)){
				return true;
			}else{
				return false;
			}  
            
        }else{
        	return false;
        }
        
    }
}