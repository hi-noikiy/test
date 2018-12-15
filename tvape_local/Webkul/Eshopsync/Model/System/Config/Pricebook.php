<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Model_System_Config_Pricebook extends Varien_Object
{
    static public function toOptionArray()
    {
    	$data = array();
    	$salesforce_pricebooks = Mage::getStoreConfig('eshopsync/default/salesforce_pricebooks');
    	if($salesforce_pricebooks){
    		$salesforce_pricebooks = Mage::helper('core')->jsonDecode($salesforce_pricebooks);
    		foreach ($salesforce_pricebooks as $key => $price_book)
    		{
    			array_push($data,
					array(
						'value' => $price_book['id'],
						'label' => $price_book['name'],
					)
				);
    		}
    		array_unshift($data, array(
				'label' => Mage::helper('eshopsync')->__('--Select Default Pricebook--'),
				'value' => '')
			);
    	}else{
			array_push($data,
				array(
					'value' => '',
					'label' => Mage::helper('eshopsync')->__("No Pricebook Found, Do Test Connection!!!"),
				)
			);
		}
		return $data;
    }
}
