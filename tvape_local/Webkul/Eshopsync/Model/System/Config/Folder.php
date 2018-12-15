<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Model_System_Config_Folder extends Varien_Object
{
	static public function toOptionArray()
	{
		$data = array();
		$salesforce_folders = Mage::getStoreConfig('eshopsync/default/salesforce_folders');
    	if($salesforce_folders){
    		$salesforce_folders = Mage::helper('core')->jsonDecode($salesforce_folders);
    		foreach ($salesforce_folders as $key => $folder)
    		{
    			array_push($data,
					array(
						'value' => $folder['id'],
						'label' => $folder['name'],
					)
				);
    		}
    		array_unshift($data, array(
				'label' => Mage::helper('eshopsync')->__('--Select Default Folder--'),
				'value' => '')
			);
    	}else{
			array_push($data,
				array(
					'value' => '',
					'label' => Mage::helper('eshopsync')->__("No Folder Found, Do Test Connection!!!"),
				)
			);
		}
		return $data;
	}
}
